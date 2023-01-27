<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use Brainshaker95\PhpToTsBundle\Exception\InvalidPropertyException;
use Brainshaker95\PhpToTsBundle\Interface\Node;
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

/**
 * @internal
 */
abstract class Converter
{
    public const TYPE_ARRAY    = 'array';
    public const TYPE_BOOLEAN  = 'bool';
    public const TYPE_CALLABLE = 'callable';
    public const TYPE_FALSE    = 'false';
    public const TYPE_FLOAT    = 'float';
    public const TYPE_INTEGER  = 'int';
    public const TYPE_ITERABLE = 'iterable';
    public const TYPE_MIXED    = 'mixed';
    public const TYPE_NULL     = 'null';
    public const TYPE_OBJECT   = 'object';
    public const TYPE_STRING   = 'string';
    public const TYPE_TRUE     = 'true';

    public const NON_ITERABLE_TYPE_MAP = [
        self::TYPE_BOOLEAN  => TsProperty::TYPE_BOOLEAN,
        self::TYPE_CALLABLE => TsProperty::TYPE_UNKNOWN,
        self::TYPE_FALSE    => TsProperty::TYPE_FALSE,
        self::TYPE_FLOAT    => TsProperty::TYPE_NUMBER,
        self::TYPE_INTEGER  => TsProperty::TYPE_NUMBER,
        self::TYPE_MIXED    => TsProperty::TYPE_UNKNOWN,
        self::TYPE_NULL     => TsProperty::TYPE_NULL,
        self::TYPE_OBJECT   => TsProperty::TYPE_UNKNOWN,
        self::TYPE_STRING   => TsProperty::TYPE_STRING,
        self::TYPE_TRUE     => TsProperty::TYPE_TRUE,
    ];

    public const ITERABLE_TYPES = [
        self::TYPE_ARRAY,
        self::TYPE_ITERABLE,
    ];

    public static function toInterface(Class_ $node): TsInterface
    {
        $name = $node->name?->name;

        Assert::nonEmptyStringNonNullable($name);

        return new TsInterface(
            name: $name,
            parentName: $node->extends ? self::getTypeName($node->extends) : null,
        );
    }

    /**
     * @throws InvalidPropertyException
     */
    public static function toProperty(
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
            $data = self::getDataFromDocComment(
                docComment: new Doc('/** @var ' . self::getTypeFromProperty($property) . ' */'),
                property: $property,
                name: $name,
                forceVarNode: true,
            );
        }

        return new TsProperty(
            name: $name,
            type: $data['rootNode'] ?? TsProperty::TYPE_UNKNOWN,
            isReadonly: $isReadonly,
            isConstructorProperty: $property instanceof Param,
            summary: $data['summary'] ?? null,
            description: $data['description'] ?? null,
            deprecation: isset($data['deprecatedNode'])
                ? implode(' ', ['@deprecated', $data['deprecatedNode']->description])
                : null,
        );
    }

    /**
     * @return array{
     *     rootNode: ?Node,
     *     description: ?string,
     *     summary: ?string,
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

        $textNode = $property instanceof Property
            ? PhpStan::getTextNode($docNode)
            : null;

        return [
            'rootNode'       => $rootNode,
            'description'    => trim($rawNode?->description ?? ''),
            'summary'        => trim($textNode?->text ?? ''),
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
     * @return string[]
     */
    private static function getSubTypes(IntersectionType|UnionType $type): array
    {
        return array_filter(array_map(
            fn (Identifier|IntersectionType|Name $subType) => $subType instanceof IntersectionType
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
