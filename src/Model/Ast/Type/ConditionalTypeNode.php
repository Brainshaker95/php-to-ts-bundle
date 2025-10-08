<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use Override;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\ConditionalTypeNode as PhpStanConditionalTypeNode;
use Stringable;

use function sprintf;

/**
 * @internal
 */
final class ConditionalTypeNode implements Node, Stringable
{
    public function __construct(
        public readonly Node $subject,
        public readonly Node $target,
        public readonly Node $if,
        public readonly Node $else,
        public readonly bool $isNegated,
    ) {}

    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    #[Override]
    public function toString(): string
    {
        return sprintf(
            '(%s extends %s ? %s : %s)',
            $this->subject,
            $this->target,
            $this->isNegated ? $this->else : $this->if,
            $this->isNegated ? $this->if : $this->else,
        );
    }

    #[Override]
    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PhpStanConditionalTypeNode::class);

        return new self(
            subject: PhpStan::toNode($node->subjectType),
            target: PhpStan::toNode($node->targetType),
            if: PhpStan::toNode($node->if),
            else: PhpStan::toNode($node->else),
            isNegated: $node->negated,
        );
    }
}
