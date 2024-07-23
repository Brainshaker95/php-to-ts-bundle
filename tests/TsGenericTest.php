<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprStringNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\UnionTypeNode;
use Brainshaker95\PhpToTsBundle\Model\TsGeneric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @PhpCsFixerIgnore heredoc_indentation
 */
#[Small]
#[CoversClass(TsGeneric::class)]
final class TsGenericTest extends TestCase
{
    public function testToString(): void
    {
        $tsGeneric = new TsGeneric('T');

        self::assertSame($tsGeneric->toString(), $tsGeneric->__toString());

        self::assertStringEqualsStringIgnoringLineEndings(<<<'EOT'
T
EOT, '' . $tsGeneric);

        self::assertStringEqualsStringIgnoringLineEndings(<<<'EOT'
U extends ('foo' | 'bar') = 'foo'
EOT, '' . new TsGeneric('U',
            bound: new UnionTypeNode([
                new ConstExprStringNode('foo'),
                new ConstExprStringNode('bar'),
            ]),
            default: new ConstExprStringNode('foo'),
        ));
    }
}
