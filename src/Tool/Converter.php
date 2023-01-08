<?php

namespace Brainshaker95\PhpToTsBundle\Tool;

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

abstract class Converter
{
    public const TYPE_ARRAY    = 'array';
    public const TYPE_BOOLEAN  = 'bool';
    public const TYPE_CALLABLE = 'callable';
    public const TYPE_FLOAT    = 'float';
    public const TYPE_INTEGER  = 'int';
    public const TYPE_ITERABLE = 'iterable';
    public const TYPE_MIXED    = 'mixed';
    public const TYPE_NULL     = 'null';
    public const TYPE_OBJECT   = 'object';
    public const TYPE_STRING   = 'string';

    public const NON_ITERABLE_TYPE_MAP = [
        self::TYPE_BOOLEAN  => TsProperty::TYPE_BOOLEAN,
        self::TYPE_CALLABLE => TsProperty::TYPE_ANY,
        self::TYPE_FLOAT    => TsProperty::TYPE_NUMBER,
        self::TYPE_INTEGER  => TsProperty::TYPE_NUMBER,
        self::TYPE_MIXED    => TsProperty::TYPE_ANY,
        self::TYPE_NULL     => TsProperty::TYPE_NULL,
        self::TYPE_OBJECT   => TsProperty::TYPE_ANY,
        self::TYPE_STRING   => TsProperty::TYPE_STRING,
    ];

    public const ITERABLE_TYPES = [
        self::TYPE_ARRAY,
        self::TYPE_ITERABLE,
    ];

    public static function toInterface(Class_ $node): TsInterface
    {
        return new TsInterface(
            name: $node->name?->name ?? 'Unknown',
            parentName: $node->extends ? self::getTypeName($node->extends) : null,
        );
    }

    public static function toProperty(
        Param|Property $property,
        bool $isReadonly,
        ?Doc $docComment = null,
    ): TsProperty {
        $type = null;

        $name = $property instanceof Param
            ? ($property->var instanceof Variable ? $property->var->name : null)
            : $property->props[0]->name->name;

        if ($property->type && !$property->type instanceof ComplexType) {
            $typeName = self::getTypeName($property->type);
            $type     = self::NON_ITERABLE_TYPE_MAP[$typeName] ?? $typeName;
        } elseif ($property->type instanceof NullableType) {
            $typeName = self::getTypeName($property->type->type);
            $type     = self::NON_ITERABLE_TYPE_MAP[$typeName] ?? $typeName;

            if ($type) {
                $type = implode(' | ', [$type, TsProperty::TYPE_NULL]);
            }
        } elseif ($property->type instanceof IntersectionType || $property->type instanceof UnionType) {
            $typeNames = self::getNonIterableTypeNames($property->type);

            if ($typeNames) {
                $type = implode($property->type instanceof IntersectionType ? ' & ' : ' | ', $typeNames);
            }
        }

        if (!$type || in_array($type, [...self::ITERABLE_TYPES, TsProperty::TYPE_ANY])) {
            $type = $docComment
                ? self::getTypeFromDocComment($property, $docComment)
                : TsProperty::TYPE_ANY;
        }

        return new TsProperty(
            name: !is_string($name) ? 'Unknown' : $name,
            type: $type,
            isReadonly: $isReadonly,
            isConstructorProperty: $property instanceof Param,
        );
    }

    private static function getTypeFromDocComment(Param|Property $property, Doc $docComment): string
    {
        // TODO: Add support for phpstan/psalm prefixes
        $typeExtractionRegex = sprintf(
            '/@%s[ \t]+(.[^ \t]*)/i',
            $property instanceof Param ? 'param' : 'var',
        );

        if (!preg_match($typeExtractionRegex, $docComment->getText(), $matches)) {
            return TsProperty::TYPE_ANY;
        }

        $match = trim($matches[1]);

        $parts = preg_split(
            pattern: '/([|&])/',
            subject: $match,
            flags: PREG_SPLIT_DELIM_CAPTURE,
        );

        return $parts
            ? implode(' ', $parts)
            : $match;
    }

    /**
     * @return ?string[]
     */
    private static function getNonIterableTypeNames(IntersectionType|UnionType $type): ?array
    {
        $typeNames = array_reduce(
            $type->types,
            function (array $names, Identifier|IntersectionType|Name $type) {
                if (!$type instanceof IntersectionType) {
                    $typeName = self::getTypeName($type);
                } else {
                    $typeNames = self::getNonIterableTypeNames($type);

                    $typeName = $typeNames
                        ? '(' . implode(' & ', $typeNames) . ')'
                        : TsProperty::TYPE_ANY;
                }

                $names[] = self::NON_ITERABLE_TYPE_MAP[$typeName]
                    ?? (!in_array($typeName, self::ITERABLE_TYPES) ? $typeName : null);

                return $names;
            },
            [],
        );

        return count(array_filter($typeNames)) === count($type->types)
            ? (in_array(TsProperty::TYPE_ANY, $typeNames) ? [TsProperty::TYPE_ANY] : array_unique($typeNames))
            : null;
    }

    private static function getTypeName(Identifier|Name $node): ?string
    {
        if ($node instanceof Name) {
            return end($node->parts) ?: null;
        }

        return $node->name;
    }
}
