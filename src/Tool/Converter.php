<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use Brainshaker95\PhpToTsBundle\Exception\InvalidPropertyException;
use Brainshaker95\PhpToTsBundle\Interface\Indentable;
use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Interface\Quotable;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ArrayShapeItemNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ArrayShapeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ArrayTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ConstTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\GenericTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\IdentifierTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\IntersectionTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\NullableTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\UnionTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Model\TsGeneric;
use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use PhpParser\Comment\Doc;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use PHPStan\PhpDocParser\Ast\PhpDoc\DeprecatedTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;

use function array_filter;
use function array_map;
use function array_unique;
use function count;
use function end;
use function get_debug_type;
use function implode;
use function in_array;
use function is_string;
use function sprintf;

/**
 * @internal
 */
abstract class Converter
{
    public const TYPE_ARRAY            = 'array';
    public const TYPE_ARRAY_KEY        = 'array-key';
    public const TYPE_BOOL             = 'bool';
    public const TYPE_BOOLEAN          = 'boolean';
    public const TYPE_CALLABLE         = 'callable';
    public const TYPE_CALLABLE_STRING  = 'callable-string';
    public const TYPE_CLASS_STRING     = 'class-string';
    public const TYPE_DOUBLE           = 'double';
    public const TYPE_FALSE            = 'false';
    public const TYPE_FLOAT            = 'float';
    public const TYPE_POSITIVE_INT     = 'positive-int';
    public const TYPE_NEGATIVE_INT     = 'negative-int';
    public const TYPE_INT              = 'int';
    public const TYPE_INT_MASK         = 'int-mask';
    public const TYPE_INTEGER          = 'integer';
    public const TYPE_ITERABLE         = 'iterable';
    public const TYPE_KEY_OF           = 'key-of';
    public const TYPE_LIST             = 'list';
    public const TYPE_LITERAL_STRING   = 'literal-string';
    public const TYPE_MIXED            = 'mixed';
    public const TYPE_NON_EMPTY_ARRAY  = 'non-empty-array';
    public const TYPE_NON_EMPTY_LIST   = 'non-empty-list';
    public const TYPE_NON_EMPTY_STRING = 'non-empty-string';
    public const TYPE_NON_FALSY_STRING = 'non-falsy-string';
    public const TYPE_NULL             = 'null';
    public const TYPE_NUMERIC_STRING   = 'numeric-string';
    public const TYPE_OBJECT           = 'object';
    public const TYPE_RESOURCE         = 'resource';
    public const TYPE_SCALAR           = 'scalar';
    public const TYPE_STATIC           = 'static';
    public const TYPE_STRING           = 'string';
    public const TYPE_THIS             = '$this';
    public const TYPE_TRUE             = 'true';
    public const TYPE_TRUTHY_STRING    = 'truthy-string';
    public const TYPE_VALUE_OF         = 'value-of';

    public const NON_ITERABLE_TYPE_MAP = [
        self::TYPE_ARRAY_KEY        => TsProperty::TYPE_STRING,
        self::TYPE_BOOL             => TsProperty::TYPE_BOOLEAN,
        self::TYPE_BOOLEAN          => TsProperty::TYPE_BOOLEAN,
        self::TYPE_CALLABLE         => TsProperty::TYPE_UNKNOWN,
        self::TYPE_CALLABLE_STRING  => TsProperty::TYPE_STRING,
        self::TYPE_CLASS_STRING     => TsProperty::TYPE_STRING,
        self::TYPE_DOUBLE           => TsProperty::TYPE_NUMBER,
        self::TYPE_FALSE            => TsProperty::TYPE_FALSE,
        self::TYPE_FLOAT            => TsProperty::TYPE_NUMBER,
        self::TYPE_INT              => TsProperty::TYPE_NUMBER,
        self::TYPE_INT_MASK         => TsProperty::TYPE_NUMBER,
        self::TYPE_INTEGER          => TsProperty::TYPE_NUMBER,
        self::TYPE_KEY_OF           => TsProperty::TYPE_STRING,
        self::TYPE_MIXED            => TsProperty::TYPE_UNKNOWN,
        self::TYPE_NULL             => TsProperty::TYPE_NULL,
        self::TYPE_LITERAL_STRING   => TsProperty::TYPE_STRING,
        self::TYPE_NEGATIVE_INT     => TsProperty::TYPE_NUMBER,
        self::TYPE_NUMERIC_STRING   => TsProperty::TYPE_STRING,
        self::TYPE_NON_EMPTY_STRING => TsProperty::TYPE_STRING,
        self::TYPE_NON_FALSY_STRING => TsProperty::TYPE_STRING,
        self::TYPE_OBJECT           => TsProperty::TYPE_UNKNOWN,
        self::TYPE_POSITIVE_INT     => TsProperty::TYPE_NUMBER,
        self::TYPE_RESOURCE         => TsProperty::TYPE_UNKNOWN,
        self::TYPE_SCALAR           => TsProperty::TYPE_UNKNOWN,
        self::TYPE_STATIC           => TsProperty::TYPE_UNKNOWN,
        self::TYPE_STRING           => TsProperty::TYPE_STRING,
        self::TYPE_THIS             => TsProperty::TYPE_THIS,
        self::TYPE_TRUE             => TsProperty::TYPE_TRUE,
        self::TYPE_TRUTHY_STRING    => TsProperty::TYPE_STRING,
        self::TYPE_VALUE_OF         => TsProperty::TYPE_STRING,
    ];

    public const ITERABLE_TYPES = [
        self::TYPE_ARRAY,
        self::TYPE_ITERABLE,
        self::TYPE_LIST,
        self::TYPE_NON_EMPTY_ARRAY,
        self::TYPE_NON_EMPTY_LIST,
    ];

    final public static function toInterface(Class_ $node, bool $isReadonly): TsInterface
    {
        $name = $node->name?->name;

        Assert::nonEmptyStringNonNullable($name);

        $docComment     = $node->getDocComment();
        $generics       = [];
        $description    = null;
        $deprecatedNode = null;

        if ($docComment) {
            $docNode        = PhpStan::getDocNode($docComment);
            $textNodes      = PhpStan::getTextNodes($docNode);
            $description    = PhpStan::textNodesToString($textNodes);
            $deprecatedNode = PhpStan::getDeprecatedNode($docNode);
            $generics       = self::getGenerics(PhpStan::getTemplateNodes($docNode));
        }

        return new TsInterface(
            name: $name,
            parentName: $node->extends ? self::getTypeName($node->extends) : null,
            isReadonly: $isReadonly,
            generics: $generics,
            description: $description ?: null,
            deprecation: $deprecatedNode ? ($deprecatedNode->description ?: true) : null,
        );
    }

    /**
     * @throws InvalidPropertyException
     */
    final public static function toProperty(
        Param|Property $property,
        bool $isReadonly,
        ?Doc $docComment,
    ): TsProperty {
        $name = self::getNameFromProperty($property);
        $data = [];

        if ($docComment) {
            $data = self::getDataFromDocComment(
                docComment: $docComment,
                property: $property,
                name: $name,
            );
        }

        if (!isset($data['rootNode'])) {
            $data['rootNode'] = self::getDataFromDocComment(
                docComment: new Doc('/** @var ' . self::getTypeFromProperty($property) . ' */'),
                property: $property,
                name: $name,
                forceVarNode: true,
            )['rootNode'];
        }

        $classIdentifiers = $data['rootNode']
            ? array_unique(self::getClassIdentifiers([$data['rootNode']]))
            : [];

        $generics = [];

        if (isset($data['templateNodes'])) {
            $generics = array_filter(
                self::getGenerics($data['templateNodes']),
                static fn (TsGeneric $generic) => in_array($generic->name, $classIdentifiers, true),
            );
        }

        $genericNames = TsGeneric::getNames($generics);

        $classIdentifiers = array_filter(
            $classIdentifiers,
            static fn (string $classIdentifier) => !in_array($classIdentifier, $genericNames, true),
        );

        return new TsProperty(
            name: $name,
            type: $data['rootNode'] ?? TsProperty::TYPE_UNKNOWN,
            isReadonly: $isReadonly,
            isConstructorProperty: $property instanceof Param,
            classIdentifiers: $classIdentifiers,
            generics: $generics,
            description: $data['description'] ?? null,
            deprecation: isset($data['deprecatedNode']) ? ($data['deprecatedNode']->description ?: true) : null,
        );
    }

    /**
     * @param Node[] $nodes
     */
    final public static function applyIndentAndQuotes(
        array $nodes,
        Indent $indent,
        Quotes $quotes,
        int $depth = 2,
    ): void {
        foreach ($nodes as $node) {
            if ($node instanceof Quotable) {
                $node->setQuotes($quotes);
            }

            if ($node instanceof Indentable) {
                $depthOffset = $node instanceof ArrayShapeNode ? -1 : 0;

                $node->setIndent($indent->withTabPresses($depth + $depthOffset));
            }

            if ($node instanceof ArrayShapeItemNode) {
                self::applyIndentAndQuotes([$node->valueNode], $indent, $quotes, $depth + 1);

                continue;
            }

            $nextLevelNodes = match (true) {
                $node instanceof ConstTypeNode         => [$node->constExpr],
                $node instanceof ArrayShapeNode        => $node->items,
                $node instanceof GenericTypeNode       => $node->genericTypes,
                self::isUnionOrIntersectionNode($node) => $node->types,
                self::isArrayOrNullableNode($node)     => match (true) {
                    $node->type instanceof ConstTypeNode,
                    $node->type instanceof ArrayShapeNode,
                    $node->type instanceof GenericTypeNode,
                    self::isUnionOrIntersectionNode($node->type) => [$node->type],
                    default                                      => [],
                },
                default => [],
            };

            if (count($nextLevelNodes)) {
                self::applyIndentAndQuotes($nextLevelNodes, $indent, $quotes, $depth);
            }
        }
    }

    /**
     * @return Node[]
     */
    final public static function getNextLevelNodes(Node $node): array
    {
        return match (true) {
            default                                => [],
            $node instanceof ArrayShapeNode        => $node->items,
            $node instanceof ArrayShapeItemNode    => [$node->valueNode],
            $node instanceof GenericTypeNode       => $node->genericTypes,
            self::isUnionOrIntersectionNode($node) => $node->types,
            self::isArrayOrNullableNode($node)     => match (true) {
                default                                      => [],
                $node->type instanceof ArrayShapeNode        => $node->type->items,
                $node->type instanceof GenericTypeNode       => $node->type->genericTypes,
                self::isUnionOrIntersectionNode($node->type) => $node->type->types,
            },
        };
    }

    final public static function getClassIdentifierNode(Node $node): ?IdentifierTypeNode
    {
        return match (true) {
            default                                                                                  => null,
            self::isClassIdentifierNode($node)                                                       => $node,
            self::isArrayOrNullableNode($node) && self::isClassIdentifierNode($node->type)           => $node->type,
            $node instanceof GenericTypeNode && $node->type->type === IdentifierTypeNode::TYPE_CLASS => $node->type,
        };
    }

    /**
     * @phpstan-assert-if-true IdentifierTypeNode $node
     */
    private static function isClassIdentifierNode(Node $node): bool
    {
        return $node instanceof IdentifierTypeNode && $node->type === IdentifierTypeNode::TYPE_CLASS;
    }

    /**
     * @phpstan-assert-if-true ArrayTypeNode|NullableTypeNode $node
     */
    private static function isArrayOrNullableNode(Node $node): bool
    {
        return $node instanceof ArrayTypeNode || $node instanceof NullableTypeNode;
    }

    /**
     * @phpstan-assert-if-true UnionTypeNode|IntersectionTypeNode $node
     */
    private static function isUnionOrIntersectionNode(Node $node): bool
    {
        return $node instanceof UnionTypeNode || $node instanceof IntersectionTypeNode;
    }

    /**
     * @return array{
     *     rootNode: ?Node,
     *     description: ?string,
     *     deprecatedNode: ?DeprecatedTagValueNode,
     *     templateNodes: TemplateTagValueNode[],
     * }
     */
    private static function getDataFromDocComment(
        Doc $docComment,
        Param|Property $property,
        string $name,
        bool $forceVarNode = false,
    ): array {
        $docNode = PhpStan::getDocNode($docComment);

        $rawNode = $property instanceof Param && !$forceVarNode
            ? PhpStan::getParamNode($docNode, $name)
            : PhpStan::getVarNode($docNode);

        $rootNode = $rawNode instanceof ParamTagValueNode || $rawNode instanceof VarTagValueNode
            ? PhpStan::toNode($rawNode->type)
            : null;

        $description = $property instanceof Param
            ? $rawNode?->description
            : PhpStan::textNodesToString(PhpStan::getTextNodes($docNode));

        return [
            'rootNode'       => $rootNode,
            'description'    => $description ?: null,
            'deprecatedNode' => PhpStan::getDeprecatedNode($docNode),
            'templateNodes'  => PhpStan::getTemplateNodes($docNode),
        ];
    }

    /**
     * @throws InvalidPropertyException
     */
    private static function getNameFromProperty(Param|Property $property): string
    {
        $name = $property instanceof Param
            ? ($property->var instanceof Variable ? $property->var->name : null)
            : $property->props[0]->name->name;

        if (!is_string($name)) {
            throw new InvalidPropertyException(sprintf(
                'Expected property name to be of type "string" but got "%s".',
                get_debug_type($name),
            ));
        }

        return $name;
    }

    private static function getTypeFromProperty(Param|Property $property): string
    {
        if ($property->type && !$property->type instanceof ComplexType) {
            $type = self::getTypeName($property->type);
        } elseif ($property->type instanceof NullableType) {
            $type = self::getTypeName($property->type->type);

            if ($type) {
                $type = '(' . implode(' | ', [$type, self::TYPE_NULL]) . ')';
            }
        } elseif ($property->type instanceof IntersectionType || $property->type instanceof UnionType) {
            $subTypes = self::getSubTypes($property->type);

            if (count($subTypes)) {
                $type = '(' . implode($property->type instanceof UnionType ? ' | ' : ' & ', $subTypes) . ')';
            }
        }

        return $type ?? self::TYPE_MIXED;
    }

    /**
     * @param TemplateTagValueNode[] $templateNodes
     *
     * @return TsGeneric[]
     */
    private static function getGenerics(array $templateNodes): array
    {
        return array_map(
            static fn (TemplateTagValueNode $node) => new TsGeneric(
                name: $node->name,
                bound: $node->bound ? PhpStan::toNode($node->bound) : null,
                default: $node->default ? PhpStan::toNode($node->default) : null,
                description: $node->description,
            ),
            $templateNodes,
        );
    }

    /**
     * @param Node[] $nodes
     * @param string[] $identifiers
     *
     * @return string[]
     */
    private static function getClassIdentifiers(array $nodes, array $identifiers = []): array
    {
        foreach ($nodes as $node) {
            $identifier = self::getClassIdentifierNode($node)?->name;

            if ($identifier) {
                $identifiers[] = $identifier;
            }

            $nextLevelNodes = self::getNextLevelNodes($node);

            if (count($nextLevelNodes)) {
                $identifiers = self::getClassIdentifiers($nextLevelNodes, $identifiers);
            }
        }

        return $identifiers;
    }

    /**
     * @return string[]
     */
    private static function getSubTypes(IntersectionType|UnionType $type): array
    {
        return array_filter(array_map(
            static fn (Identifier|IntersectionType|Name $subType) => $subType instanceof IntersectionType
                ? '(' . implode(' & ', self::getSubTypes($subType)) . ')'
                : self::getTypeName($subType),
            $type->types,
        ));
    }

    private static function getTypeName(Identifier|Name $node): ?string
    {
        return $node instanceof Name
            ? (end($node->parts) ?: null)
            : $node->name;
    }
}
