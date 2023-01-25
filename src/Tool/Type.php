<?php

namespace Brainshaker95\PhpToTsBundle\Tool;

use Brainshaker95\PhpToTsBundle\Exception\UnsupportedNodeException;
use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprFalseNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprFloatNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprIntegerNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprNullNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprStringNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprTrueNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstFetchNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ArrayShapeItemNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ArrayShapeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ArrayTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ConstTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\GenericTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\IdentifierTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\IntersectionTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\NullableTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode as PHPStanConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFloatNode as PHPStanConstExprFloatNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode as PHPStanConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNullNode as PHPStanConstExprNullNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode as PHPStanConstExprStringNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode as PHPStanConstExprTrueNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode as PHPStanConstFetchNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode as PHPStanArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode as PHPStanArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode as PHPStanArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode as PHPStanConstTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode as PHPStanGenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode as PHPStanIdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode as PHPStanIntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode as PHPStanNullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode as PHPStanUnionTypeNode;

abstract class Type
{
    /**
     * @var array<class-string<PHPStanNode>,class-string<Node>>
     */
    public const NODE_CLASS_MAP = [
        PHPStanArrayShapeItemNode::class   => ArrayShapeItemNode::class,
        PHPStanArrayShapeNode::class       => ArrayShapeNode::class,
        PHPStanArrayTypeNode::class        => ArrayTypeNode::class,
        PHPStanConstTypeNode::class        => ConstTypeNode::class,
        PHPStanGenericTypeNode::class      => GenericTypeNode::class,
        PHPStanIdentifierTypeNode::class   => IdentifierTypeNode::class,
        PHPStanIntersectionTypeNode::class => IntersectionTypeNode::class,
        PHPStanNullableTypeNode::class     => NullableTypeNode::class,
        PHPStanUnionTypeNode::class        => UnionTypeNode::class,
        PHPStanConstExprTrueNode::class    => ConstExprTrueNode::class,
        PHPStanConstExprFalseNode::class   => ConstExprFalseNode::class,
        PHPStanConstExprFloatNode::class   => ConstExprFloatNode::class,
        PHPStanConstExprIntegerNode::class => ConstExprIntegerNode::class,
        PHPStanConstExprNullNode::class    => ConstExprNullNode::class,
        PHPStanConstExprStringNode::class  => ConstExprStringNode::class,
        PHPStanConstFetchNode::class       => ConstFetchNode::class,
    ];

    public static function fromPhpStan(PHPStanNode $node): Node
    {
        $nodeClass = self::NODE_CLASS_MAP[get_class($node)] ?? null;

        if (!$nodeClass) {
            throw new UnsupportedNodeException(sprintf(
                'Unsupported node type "%s".',
                get_class($node),
            ));
        }

        return $nodeClass::fromPhpStan($node);
    }
}
