<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Service;

use Brainshaker95\PhpToTsBundle\Event\TsInterfaceGeneratedEvent;
use Brainshaker95\PhpToTsBundle\Event\TsPropertyGeneratedEvent;
use Brainshaker95\PhpToTsBundle\Interface\TypeScriptable;
use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @internal
 */
class Visitor extends NodeVisitorAbstract
{
    #[Required]
    public EventDispatcherInterface $eventDispatcher;

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
            $this->addTsProperty($node, $node->isReadonly(), $docComment);
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
                fn (Param $param, bool $isReadonly) => $this->addTsProperty($param, $isReadonly, $docComment),
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
            $event = $this->eventDispatcher->dispatch(new TsInterfaceGeneratedEvent(
                tsInterface: $this->currentTsInterface,
                classNode: $node,
            ));

            if ($event->tsInterface) {
                $this->tsInterfaces[] = $event->tsInterface;
            }
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

    private function addTsProperty(
        Param|Property $property,
        bool $isReadonly,
        ?Doc $docComment,
    ): void {
        $tsProperty = Converter::toProperty($property, $isReadonly, $docComment);

        $event = $this->eventDispatcher->dispatch(new TsPropertyGeneratedEvent(
            tsProperty: $tsProperty,
            propertyNode: $property,
        ));

        if ($event->tsProperty) {
            $this->currentTsInterface?->addProperty($event->tsProperty);
        }
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
