<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ArrayShapeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\GenericTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\IntersectionTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\NullableTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\UnionTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Stringable;

class TsProperty implements Stringable
{
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_FALSE   = 'false';
    public const TYPE_NULL    = 'null';
    public const TYPE_NUMBER  = 'number';
    public const TYPE_STRING  = 'string';
    public const TYPE_TRUE    = 'true';
    public const TYPE_UNKNOWN = 'unknown';

    /**
     * @param self::TYPE_UNKNOWN|Node $type
     */
    public function __construct(
        public string $name,
        public string|Node $type,
        public readonly bool $isReadonly = false,
        public readonly bool $isConstructorProperty = false,
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly ?string $deprecation = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(Indent $indent = new Indent()): string
    {
        if ($this->type instanceof Node) {
            $this->applyIndent([$this->type], $indent);
        }

        return sprintf(
            '%s%s%s%s: %s;',
            $indent->toString(),
            $this->getDocComment($indent),
            $this->isReadonly ? 'readonly ' : '',
            $this->name,
            $this->type,
        );
    }

    private function getDocComment(Indent $indent): string
    {
        if (!$this->summary && !$this->description && !$this->deprecation) {
            return '';
        }

        $docComment       = '/**' . PHP_EOL;
        $linePrefix       = $indent->toString() . ' * ';
        $summaryLines     = $this->summary ? Str::splitByNewLines($this->summary, $linePrefix) : null;
        $descriptionLines = $this->description ? Str::splitByNewLines($this->description, $linePrefix) : null;
        $hasSummary       = !empty($summaryLines);
        $hasDescription   = !empty($descriptionLines);

        if ($hasSummary) {
            $docComment .= implode(PHP_EOL, $summaryLines) . PHP_EOL;
        }

        if ($hasDescription) {
            $docComment .= implode(PHP_EOL, $descriptionLines) . PHP_EOL;
        }

        if ($this->deprecation) {
            if ($hasSummary || $hasDescription) {
                $docComment .= $linePrefix . PHP_EOL;
            }

            $docComment .= $linePrefix . $this->deprecation . PHP_EOL;
        }

        $docComment .= $indent->toString() . ' */' . PHP_EOL
            . $indent->toString();

        return $docComment;
    }

    /**
     * @param Node[] $nodes
     */
    private function applyIndent(array $nodes, Indent $indent, int $depth = 2): void
    {
        foreach ($nodes as $node) {
            if ($node instanceof ArrayShapeNode) {
                $node->setIndent($indent->withTabPresses($depth - 1));

                foreach ($node->items as $item) {
                    $item->setIndent($indent->withTabPresses($depth));
                    $this->applyIndent([$item->valueNode], $indent, $depth + 1);
                }

                continue;
            }

            if ($node instanceof UnionTypeNode || $node instanceof IntersectionTypeNode) {
                $this->applyIndent($node->types, $indent, $depth);

                continue;
            }

            if ($node instanceof GenericTypeNode) {
                $this->applyIndent($node->genericTypes, $indent, $depth);

                continue;
            }

            if ($node instanceof NullableTypeNode) {
                if ($node->type instanceof ArrayShapeNode) {
                    $this->applyIndent([$node->type], $indent, $depth);

                    continue;
                }

                if ($node->type instanceof UnionTypeNode || $node->type instanceof IntersectionTypeNode) {
                    $this->applyIndent($node->type->types, $indent, $depth);
                }
            }
        }
    }
}
