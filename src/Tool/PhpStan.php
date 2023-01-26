<?php

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

    public static function toNode(PHPStanNode $node): Node
    {
        $nodeClass = self::NODE_CLASS_MAP[get_class($node)] ?? null;

        if (!$nodeClass) {
            throw new UnsupportedNodeException(sprintf(
                'Unsupported node type "%s".',
                get_class($node),
            ));
        }

        return Closure::fromCallable([$nodeClass, 'fromPhpStan'])($node);
    }
}
