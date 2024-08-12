<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Service;

use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;
use Brainshaker95\PhpToTsBundle\Event\TsEnumGeneratedEvent;
use Brainshaker95\PhpToTsBundle\Event\TsInterfaceGeneratedEvent;
use Brainshaker95\PhpToTsBundle\Event\TsPropertyGeneratedEvent;
use Brainshaker95\PhpToTsBundle\Interface\Config;
use Brainshaker95\PhpToTsBundle\Model\TsEnum;
use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Service\Traits\HasEventDispatcher;
use Brainshaker95\PhpToTsBundle\Tool\Attribute;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PhpParser\NodeVisitor\NameResolver;

use function array_filter;
use function array_map;
use function implode;

/**
 * @internal
 */
final class Visitor extends NameResolver
{
    use HasEventDispatcher;

    public ?Config $config = null;

    private bool $isTypeScriptable;

    private ?TsInterface $currentTsInterface;

    private ?TsEnum $currentTsEnum;

    /**
     * @var array<string, string>
     */
    private array $currentClassNameMap;

    /**
     * @var TsInterface[]
     */
    private array $tsInterfaces;

    /**
     * @var TsEnum[]
     */
    private array $tsEnums;

    /**
     * @param Node[] $nodes
     *
     * @return ?Node[]
     */
    public function beforeTraverse(array $nodes): ?array
    {
        parent::beforeTraverse($nodes);

        $this->isTypeScriptable    = false;
        $this->currentTsInterface  = null;
        $this->currentTsEnum       = null;
        $this->currentClassNameMap = [];
        $this->tsInterfaces        = [];
        $this->tsEnums             = [];

        return null;
    }

    /**
     * @return int|Node|null
     */
    public function enterNode(Node $node)
    {
        parent::enterNode($node);

        if ($node instanceof Use_ || $node instanceof GroupUse) {
            if ($node instanceof Use_ && $node->type !== Use_::TYPE_NORMAL) {
                return null;
            }

            $uses = $node instanceof Use_
                ? $node->uses
                : array_filter(
                    $node->uses,
                    static fn (UseItem $use): bool => $use->type === Use_::TYPE_NORMAL,
                );

            foreach ($uses as $use) {
                $this->currentClassNameMap[$use->getAlias()->name] = $use->name->name;
            }

            return null;
        }

        if (($node instanceof Class_ || $node instanceof Enum_)
            && !$this->isTypeScriptable && self::isTypeScriptable($node)) {
            $fqcn                                                     = self::getFqcn($node);
            $this->isTypeScriptable                                   = true;
            $this->currentClassNameMap['self']                        = $fqcn;
            $this->currentClassNameMap[Str::getShortClassName($fqcn)] = $fqcn;

            if ($node instanceof Class_) {
                $this->currentTsInterface = Converter::toInterface($node, $node->isReadonly());
            } else {
                $this->currentTsEnum = Converter::toEnum($node);
            }

            return null;
        }

        if (!$this->currentTsInterface && !$this->currentTsEnum) {
            return null;
        }

        $docComment = $node->getDocComment();

        if ($node instanceof Property && $node->isPublic()) {
            $this->addTsProperty(
                property: $node,
                isReadonly: $this->currentTsInterface?->isReadonly ? true : $node->isReadonly(),
                docComment: $docComment,
            );

            return null;
        }

        if ($node instanceof ClassMethod && $node->name->name === '__construct') {
            $publicParams = array_filter(
                $node->params,
                static fn (Param $param) => ($param->flags & Modifiers::PUBLIC) !== 0,
            );

            $readonlyStates = array_map(
                static fn (Param $param) => ($param->flags & Modifiers::READONLY) !== 0,
                $publicParams,
            );

            array_map(
                fn (Param $param, bool $isReadonly) => $this->addTsProperty(
                    property: $param,
                    isReadonly: $this->currentTsInterface?->isReadonly ? true : $isReadonly,
                    docComment: $docComment,
                ),
                $publicParams,
                $readonlyStates,
            );

            return null;
        }

        if ($node instanceof EnumCase) {
            $this->addTsProperty(
                property: $node,
                docComment: $docComment,
            );

            return null;
        }

        return null;
    }

    /**
     * @return int|Node|Node[]|null
     */
    public function leaveNode(Node $node)
    {
        parent::leaveNode($node);

        if ($node instanceof Class_ && $this->currentTsInterface) {
            $this->isTypeScriptable           = false;
            $this->currentTsInterface->config = $this->config;

            $event = $this->eventDispatcher->dispatch(new TsInterfaceGeneratedEvent(
                tsInterface: $this->currentTsInterface,
                classNode: $node,
            ));

            if ($event->tsInterface) {
                $this->tsInterfaces[] = $event->tsInterface;
            }
        }

        if ($node instanceof Enum_ && $this->currentTsEnum) {
            $this->isTypeScriptable      = false;
            $this->currentTsEnum->config = $this->config;

            $event = $this->eventDispatcher->dispatch(new TsEnumGeneratedEvent(
                tsEnum: $this->currentTsEnum,
                enumNode: $node,
            ));

            if ($event->tsEnum) {
                $this->tsEnums[] = $event->tsEnum;
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

    /**
     * Gets all created TsEnum instances aggregated during traversal.
     *
     * @return TsEnum[]
     */
    public function getTsEnums(): array
    {
        return $this->tsEnums;
    }

    private function addTsProperty(
        Param|Property|EnumCase $property,
        bool $isReadonly = false,
        ?Doc $docComment = null,
    ): void {
        $tsProperty         = Converter::toProperty($property, $isReadonly, $docComment, $this->currentClassNameMap);
        $tsProperty->config = $this->config;

        $event = $this->eventDispatcher->dispatch(new TsPropertyGeneratedEvent(
            tsProperty: $tsProperty,
            propertyNode: $property,
        ));

        if ($event->tsProperty) {
            if ($property instanceof EnumCase) {
                $this->currentTsEnum?->addProperty($event->tsProperty);
            } else {
                $this->currentTsInterface?->addProperty($event->tsProperty);
            }
        }
    }

    private static function isTypeScriptable(ClassLike $node): bool
    {
        $fcqn = self::getFqcn($node);

        return $fcqn
            ? Attribute::existsOnClass(AsTypeScriptable::class, $fcqn)
            : false;
    }

    /**
     * @return ?class-string
     */
    private static function getFqcn(ClassLike $node): ?string
    {
        /**
         * @var ?class-string
         */
        return $node->namespacedName
            ? implode('\\', $node->namespacedName->getParts())
            : $node->name?->name;
    }
}
