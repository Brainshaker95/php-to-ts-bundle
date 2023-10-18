<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use Brainshaker95\PhpToTsBundle\Exception\UnsupportedNodeException;
use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use PhpParser\Comment\Doc;
use PHPStan\PhpDocParser\Ast\ConstExpr as PHPStanConstExpr;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\DeprecatedTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TypelessParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type as PHPStanType;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

use function array_filter;
use function array_is_list;
use function array_map;
use function current;
use function implode;
use function is_array;
use function is_iterable;
use function sprintf;

/**
 * @internal
 */
final class PhpStan
{
    /**
     * @var array<class-string<PHPStanNode>,class-string<Node>>
     */
    private const NODE_CLASS_MAP = [
        PHPStanConstExpr\ConstExprFalseNode::class   => ConstExpr\ConstExprFalseNode::class,
        PHPStanConstExpr\ConstExprFloatNode::class   => ConstExpr\ConstExprFloatNode::class,
        PHPStanConstExpr\ConstExprIntegerNode::class => ConstExpr\ConstExprIntegerNode::class,
        PHPStanConstExpr\ConstExprNullNode::class    => ConstExpr\ConstExprNullNode::class,
        PHPStanConstExpr\ConstExprStringNode::class  => ConstExpr\ConstExprStringNode::class,
        PHPStanConstExpr\ConstExprTrueNode::class    => ConstExpr\ConstExprTrueNode::class,
        PHPStanConstExpr\ConstFetchNode::class       => ConstExpr\ConstFetchNode::class,
        PHPStanType\ArrayShapeItemNode::class        => Type\ArrayShapeItemNode::class,
        PHPStanType\ArrayShapeNode::class            => Type\ArrayShapeNode::class,
        PHPStanType\ArrayTypeNode::class             => Type\ArrayTypeNode::class,
        PHPStanType\ConstTypeNode::class             => Type\ConstTypeNode::class,
        PHPStanType\GenericTypeNode::class           => Type\GenericTypeNode::class,
        PHPStanType\IdentifierTypeNode::class        => Type\IdentifierTypeNode::class,
        PHPStanType\IntersectionTypeNode::class      => Type\IntersectionTypeNode::class,
        PHPStanType\NullableTypeNode::class          => Type\NullableTypeNode::class,
        PHPStanType\UnionTypeNode::class             => Type\UnionTypeNode::class,
    ];

    private static ConstExprParser $constExprParser;

    private static Lexer $lexer;

    private static PhpDocParser $phpDocParser;

    private function __construct() {}

    public static function toNode(PHPStanNode $node): Node
    {
        $nodeClass = self::NODE_CLASS_MAP[$node::class] ?? null;

        if (!$nodeClass) {
            throw new UnsupportedNodeException(sprintf(
                'Unsupported node type "%s".',
                $node::class,
            ));
        }

        return $nodeClass::fromPhpStan($node);
    }

    public static function getDocNode(Doc $docComment): PhpDocNode
    {
        self::$lexer           ??= new Lexer();
        self::$constExprParser ??= new ConstExprParser();
        self::$phpDocParser    ??= new PhpDocParser(new TypeParser(self::$constExprParser), self::$constExprParser);

        return self::$phpDocParser->parse(new TokenIterator(
            self::$lexer->tokenize($docComment->getText()),
        ));
    }

    public static function getParamNode(PhpDocNode $docNode, string $name): ParamTagValueNode|TypelessParamTagValueNode|null
    {
        $values = [
            ...$docNode->getParamTagValues('@param'),
            ...$docNode->getParamTagValues('@phpstan-param'),
            ...$docNode->getParamTagValues('@psalm-param'),
            ...$docNode->getTypelessParamTagValues('@param'),
            ...$docNode->getTypelessParamTagValues('@phpstan-param'),
            ...$docNode->getTypelessParamTagValues('@psalm-param'),
        ];

        return current(array_filter(
            $values,
            static fn (ParamTagValueNode|TypelessParamTagValueNode $node) => $node->parameterName === '$' . $name,
        )) ?: null;
    }

    public static function getVarNode(PhpDocNode $docNode): ?VarTagValueNode
    {
        $values = [
            ...$docNode->getVarTagValues('@var'),
            ...$docNode->getVarTagValues('@phpstan-var'),
            ...$docNode->getVarTagValues('@psalm-var'),
        ];

        return current($values) ?: null;
    }

    public static function getDeprecatedNode(PhpDocNode $docNode): ?DeprecatedTagValueNode
    {
        return current($docNode->getDeprecatedTagValues()) ?: null;
    }

    /**
     * @return TemplateTagValueNode[]
     */
    public static function getTemplateNodes(PhpDocNode $docNode): array
    {
        return [
            ...$docNode->getTemplateTagValues('@template'),
            ...$docNode->getTemplateTagValues('@phpstan-template'),
            ...$docNode->getTemplateTagValues('@psalm-template'),
        ];
    }

    /**
     * @return PhpDocTextNode[]
     */
    public static function getTextNodes(PhpDocNode $docNode): array
    {
        return array_filter(
            $docNode->children,
            static fn (PhpDocChildNode $childNode) => $childNode instanceof PhpDocTextNode && $childNode->text,
        );
    }

    /**
     * @param PhpDocTextNode[] $textNodes
     */
    public static function textNodesToString(array $textNodes): string
    {
        return implode("\n", array_map(
            static fn (PhpDocTextNode $textNode) => $textNode->text,
            $textNodes,
        ));
    }

    public static function phpValueToTsType(
        mixed $value,
        Indent $indent = new Indent(),
        Quotes $quotes = new Quotes(),
    ): string {
        if (!is_iterable($value)) {
            return self::phpValueToNode($value)->toString();
        }

        $itemNodes = [];
        $hasKeys   = is_array($value) ? !array_is_list($value) : false;

        foreach ($value as $itemKey => $itemValue) {
            $itemNodes[] = new Type\ArrayShapeItemNode(
                valueNode: self::phpValueToNode($itemValue),
                keyNode: $hasKeys ? new ConstExpr\ConstExprStringNode($itemKey) : null,
            );
        }

        $shapeNode = new Type\ArrayShapeNode($itemNodes);

        Converter::applyIndentAndQuotes([$shapeNode], $indent, $quotes);

        return $shapeNode->toString();
    }

    public static function phpValueToNode(mixed $value): Node
    {
        $docComment = new Doc('/** @var ' . Str::displayType($value) . ' */');
        $docNode    = self::getDocNode($docComment);
        $varNode    = self::getVarNode($docNode);
        $node       = new Type\IdentifierTypeNode(TsProperty::TYPE_UNKNOWN);

        if ($varNode) {
            try {
                $node = self::toNode($varNode->type);
            } catch (UnsupportedNodeException) {
            }
        }

        return $node;
    }
}
