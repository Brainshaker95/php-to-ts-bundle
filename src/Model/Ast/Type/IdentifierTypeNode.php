<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode as PHPStanIdentifierTypeNode;

use function array_key_exists;
use function in_array;
use function str_contains;

/**
 * @internal
 */
final class IdentifierTypeNode implements Node
{
    public const TYPE_CLASS   = 'class';
    public const TYPE_DEFAULT = 'default';

    /**
     * @phpstan-param self::TYPE_* $type
     */
    public function __construct(
        public string $name,
        public readonly string $type = self::TYPE_DEFAULT,
    ) {}

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->name;
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanIdentifierTypeNode::class);

        $name = $node->name;
        $type = self::TYPE_DEFAULT;

        if (array_key_exists($name, Converter::NON_ITERABLE_TYPE_MAP)) {
            $name = Converter::NON_ITERABLE_TYPE_MAP[$name];
        } elseif (in_array($name, Converter::ITERABLE_TYPES, true)) {
            $name = TsProperty::TYPE_UNKNOWN . '[]';
        } elseif ($name === '\stdClass' || $name === 'stdClass') {
            $name = TsProperty::TYPE_UNKNOWN;
        } elseif (self::isInterpretedAsClass($name)) {
            $name = self::getShortClassName($name);
            $type = self::TYPE_CLASS;
        }

        return new self(
            name: $name,
            type: $type,
        );
    }

    private static function isInterpretedAsClass(string $name): bool
    {
        return $name[0] === Str::toUpper($name[0]);
    }

    private static function getShortClassName(string $name): string
    {
        return str_contains($name, '\\')
            ? Str::afterLast($name, '\\')
            : $name;
    }
}
