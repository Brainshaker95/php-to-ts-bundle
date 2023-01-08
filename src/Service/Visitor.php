<?php

namespace Brainshaker95\PhpToTsBundle\Service;

use Brainshaker95\PhpToTsBundle\Interface\TypeScriptable;
use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;

class Visitor extends NodeVisitorAbstract
{
    private bool $isTypeScriptable;

    private ?string $typeScriptableAlias;

    private ?TsInterface $currentTsInterface;

    /**
     * @var TsInterface[]
     */
    private ?array $tsInterfaces;

    /**
     * @param Node[] $nodes
     *
     * @return ?Node[]
     */
    public function beforeTraverse(array $nodes)
    {
        $this->isTypeScriptable    = false;
        $this->typeScriptableAlias = null;
        $this->currentTsInterface  = null;
        $this->tsInterfaces        = [];

        return null;
    }

    /**
     * @return int|Node|null
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof UseUse
            && !$this->typeScriptableAlias
            && $node->alias
            && $this->endsWithTypeScriptable($node->name->parts)) {
            $this->typeScriptableAlias = $node->alias->name;
        }

        if ($node instanceof Class_ && !$this->isTypeScriptable) {
            foreach ($node->implements as $implement) {
                if (!$this->endsWithTypeScriptable($implement->parts)) {
                    return null;
                }

                $this->isTypeScriptable   = true;
                $this->currentTsInterface = Converter::toInterface($node);

                break;
            }
        }

        if (!$this->currentTsInterface) {
            return null;
        }

        $docComment = $node->getDocComment();

        if ($node instanceof Property && $node->isPublic()) {
            $this->currentTsInterface->addProperty(
                Converter::toProperty($node, $node->isReadonly(), $docComment),
            );
        }

        if ($node instanceof ClassMethod && $node->name->name === '__construct') {
            $publicParams = array_filter(
                $node->params,
                fn (Param $param) => ($param->flags & Class_::MODIFIER_PUBLIC) !== 0,
            );

            $readonlyStates = array_map(
                fn (Param $param) => ($param->flags & Class_::MODIFIER_READONLY) !== 0,
                $publicParams,
            );

            array_map(
                fn (Param $param, bool $isReadonly) => $this->currentTsInterface->addProperty(
                    Converter::toProperty($param, $isReadonly, $docComment),
                ),
                $publicParams,
                $readonlyStates,
            );
        }

        return null;
    }

    /**
     * @return int|Node|Node[]|null
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Class_) {
            return null;
        }

        $this->isTypeScriptable = false;

        if ($this->currentTsInterface) {
            $this->tsInterfaces[] = $this->currentTsInterface;
        }

        return null;
    }

    /**
     * Gets all created TsInterface instances aggregated during traversal.
     *
     * @return TsInterface[]
     */
    public function getTsInterfaces(): ?array
    {
        return $this->tsInterfaces;
    }

    /**
     * @param string[] $parts
     */
    private function endsWithTypeScriptable(array $parts): bool
    {
        $lastPart = end($parts);

        return $lastPart === (new ReflectionClass(TypeScriptable::class))->getShortName()
            || $lastPart === $this->typeScriptableAlias;
    }
}
