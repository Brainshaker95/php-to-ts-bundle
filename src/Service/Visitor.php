<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Service;

use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;
use Brainshaker95\PhpToTsBundle\Event\TsInterfaceGeneratedEvent;
use Brainshaker95\PhpToTsBundle\Event\TsPropertyGeneratedEvent;
use Brainshaker95\PhpToTsBundle\Interface\Config;
use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Tool\Attribute;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitor\NameResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\Required;

use function array_filter;
use function array_map;
use function implode;

/**
 * @internal
 */
final class Visitor extends NameResolver
{
    #[Required]
    public EventDispatcherInterface $eventDispatcher;

    public ?Config $config = null;

    private bool $isTypeScriptable;

    private ?TsInterface $currentTsInterface;

    /**
     * @var TsInterface[]
     */
    private array $tsInterfaces;

    /**
     * @param Node[] $nodes
     *
     * @return ?Node[]
     */
    public function beforeTraverse(array $nodes)
    {
        parent::beforeTraverse($nodes);

        $this->isTypeScriptable   = false;
        $this->currentTsInterface = null;
        $this->tsInterfaces       = [];

        return null;
    }

    /**
     * @return int|Node|null
     */
    public function enterNode(Node $node)
    {
        parent::enterNode($node);

        if ($node instanceof Class_ && !$this->isTypeScriptable && self::isTypeScriptable($node)) {
            $this->isTypeScriptable   = true;
            $this->currentTsInterface = Converter::toInterface($node, $node->isReadonly());
        }

        if (!$this->currentTsInterface) {
            return null;
        }

        $docComment = $node->getDocComment();

        if ($node instanceof Property && $node->isPublic()) {
            $this->addTsProperty(
                property: $node,
                isReadonly: $this->currentTsInterface->isReadonly ? true : $node->isReadonly(),
                docComment: $docComment,
            );

            return null;
        }

        if ($node instanceof ClassMethod && $node->name->name === '__construct') {
            $publicParams = array_filter(
                $node->params,
                static fn (Param $param) => ($param->flags & Class_::MODIFIER_PUBLIC) !== 0,
            );

            $readonlyStates = array_map(
                static fn (Param $param) => ($param->flags & Class_::MODIFIER_READONLY) !== 0,
                $publicParams,
            );

            array_map(
                fn (Param $param, bool $isReadonly) => $this->addTsProperty(
                    property: $param,
                    isReadonly: $this->currentTsInterface->isReadonly ? true : $isReadonly,
                    docComment: $docComment,
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
        parent::leaveNode($node);

        if (!$node instanceof Class_) {
            return null;
        }

        $this->isTypeScriptable = false;

        if ($this->currentTsInterface) {
            $this->currentTsInterface->config = $this->config;

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
    public function getTsInterfaces(): array
    {
        return $this->tsInterfaces;
    }

    private function addTsProperty(
        Param|Property $property,
        bool $isReadonly,
        ?Doc $docComment,
    ): void {
        $tsProperty         = Converter::toProperty($property, $isReadonly, $docComment);
        $tsProperty->config = $this->config;

        $event = $this->eventDispatcher->dispatch(new TsPropertyGeneratedEvent(
            tsProperty: $tsProperty,
            propertyNode: $property,
        ));

        if ($event->tsProperty) {
            $this->currentTsInterface?->addProperty($event->tsProperty);
        }
    }

    private static function isTypeScriptable(Class_ $node): bool
    {
        $fcqn = self::getFqcn($node);

        return $fcqn
            ? Attribute::existsOnClass(AsTypeScriptable::class, $fcqn)
            : false;
    }

    /**
     * @return ?class-string
     */
    private static function getFqcn(Class_ $node): ?string
    {
        /**
         * @var ?class-string
         */
        return $node->namespacedName
            ? implode('\\', $node->namespacedName->parts)
            : $node->name?->name;
    }
}
