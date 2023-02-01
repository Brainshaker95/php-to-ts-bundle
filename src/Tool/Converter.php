<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use Brainshaker95\PhpToTsBundle\Exception\InvalidPropertyException;
use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Interface\QuotesAware;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ArrayShapeNode;
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
use function end;
use function get_debug_type;
use function implode;
use function is_string;
use function sprintf;
use function trim;

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

    final public static function toInterface(Class_ $node): TsInterface
    {
        $name = $node->name?->name;

        Assert::nonEmptyStringNonNullable($name);

        $docComment     = $node->getDocComment();
        $textNode       = null;
        $deprecatedNode = null;

        if ($docComment) {
            $docNode        = PhpStan::getDocNode($docComment);
            $textNode       = PhpStan::getTextNode($docNode);
            $deprecatedNode = PhpStan::getDeprecatedNode($docNode);
        }

        return new TsInterface(
            name: $name,
            parentName: $node->extends ? self::getTypeName($node->extends) : null,
            description: $textNode ? trim($textNode->text) : null,
            deprecation: $deprecatedNode
                ? implode(' ', ['@deprecated', $deprecatedNode->description])
                : null,
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

        return new TsProperty(
            name: $name,
            type: $data['rootNode'] ?? TsProperty::TYPE_UNKNOWN,
            isReadonly: $isReadonly,
            isConstructorProperty: $property instanceof Param,
            classIdentifiers: $classIdentifiers,
            generics: isset($data['templateNodes']) ? self::getGenerics($data['templateNodes']) : [],
            description: $data['description'] ?? null,
            deprecation: isset($data['deprecatedNode'])
                ? implode(' ', ['@deprecated', $data['deprecatedNode']->description])
                : null,
        );
    }

    /**
     * @param Node[] $nodes
     */
    final public static function applyIndentAndQuotes(
        array $nodes,
        Indent $indent,
        Quotes $quotes,
        int $depth = 1,
    ): void {
        foreach ($nodes as $node) {
            if ($node instanceof QuotesAware) {
                $node->setQuotes($quotes);
            }

            if ($node instanceof ArrayShapeNode) {
                $node->setIndent($indent->withTabPresses($depth - 1));

                foreach ($node->items as $item) {
                    $item->setIndent($indent->withTabPresses($depth));
                    self::applyIndentAndQuotes([$item->valueNode], $indent, $quotes, $depth + 1);
                }

                continue;
            }

            if ($node instanceof UnionTypeNode || $node instanceof IntersectionTypeNode) {
                self::applyIndentAndQuotes($node->types, $indent, $quotes, $depth);

                continue;
            }

            if ($node instanceof GenericTypeNode) {
                self::applyIndentAndQuotes($node->genericTypes, $indent, $quotes, $depth);

                continue;
            }

            if ($node instanceof NullableTypeNode) {
                if ($node->type instanceof ArrayShapeNode) {
                    self::applyIndentAndQuotes([$node->type], $indent, $quotes, $depth);

                    continue;
                }

                if ($node->type instanceof UnionTypeNode || $node->type instanceof IntersectionTypeNode) {
                    self::applyIndentAndQuotes($node->type->types, $indent, $quotes, $depth);
                }
            }
        }
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

        $description = $property instanceof Property
            ? trim(PhpStan::getTextNode($docNode)?->text ?? '')
            : trim($rawNode?->description ?? '');

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

            if (!empty($subTypes)) {
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
            if ($node instanceof IdentifierTypeNode && $node->type === IdentifierTypeNode::TYPE_CLASS) {
                $identifiers[] = $node->name;
            }

            if ($node instanceof ArrayShapeNode) {
                foreach ($node->items as $item) {
                    $identifiers = self::getClassIdentifiers([$item->valueNode], $identifiers);
                }

                continue;
            }

            if ($node instanceof UnionTypeNode || $node instanceof IntersectionTypeNode) {
                $identifiers = self::getClassIdentifiers($node->types, $identifiers);

                continue;
            }

            if ($node instanceof GenericTypeNode) {
                $identifiers = self::getClassIdentifiers($node->genericTypes, $identifiers);

                continue;
            }

            if ($node instanceof NullableTypeNode) {
                if ($node->type instanceof ArrayShapeNode) {
                    $identifiers = self::getClassIdentifiers([$node->type], $identifiers);

                    continue;
                }

                if ($node->type instanceof UnionTypeNode || $node->type instanceof IntersectionTypeNode) {
                    $identifiers = self::getClassIdentifiers($node->type->types, $identifiers);
                }
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
        if ($node instanceof Name) {
            return end($node->parts) ?: null;
        }

        return $node->name;
    }
}
