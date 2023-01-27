<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use Brainshaker95\PhpToTsBundle\Exception\UnsupportedNodeException;
use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type;
use Closure;
use PhpParser\Comment\Doc;
use PHPStan\PhpDocParser\Ast\ConstExpr as PHPStanConstExpr;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\DeprecatedTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TypelessParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type as PHPStanType;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

/**
 * @internal
 */
abstract class PhpStan
{
    /**
     * @var array<class-string<PHPStanNode>,class-string<Node>>
     */
    public const NODE_CLASS_MAP = [
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

    public static function toNode(PHPStanNode $node): Node
    {
        /**
         * @var ?class-string<Node>
         */
        $nodeClass = self::NODE_CLASS_MAP[get_class($node)] ?? null;

        if (!$nodeClass) {
            throw new UnsupportedNodeException(sprintf(
                'Unsupported node type "%s".',
                get_class($node),
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
            fn (ParamTagValueNode|TypelessParamTagValueNode $node) => $node->parameterName === '$' . $name,
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
}
