<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\TsDocComment;
use Brainshaker95\PhpToTsBundle\Model\TsGeneric;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @small
 *
 * @covers \Brainshaker95\PhpToTsBundle\Model\TsDocComment
 *
 * @PhpCsFixerIgnore heredoc_indentation
 */
final class TsDocCommentTest extends TestCase
{
    public function testToString(): void
    {
        $tsDocComment = new TsDocComment(
            description: 'This is a description',
            deprecation: 'This is a deprecation',
            generics: [
                new TsGeneric(
                    name: 'Foo',
                    description: 'This is a generic description',
                ),
                new TsGeneric(
                    name: 'Bar',
                    description: 'This is another generic description',
                ),
                new TsGeneric(
                    name: 'Baz',
                ),
            ],
        );

        self::assertSame($tsDocComment->toString(), $tsDocComment->__toString());

        self::assertStringEqualsStringIgnoringLineEndings(<<<'EOT'
/**
 * This is a description
 *
 * @deprecated This is a deprecation
 *
 * @template Foo This is a generic description
 * @template Bar This is another generic description
 */
EOT, '' . $tsDocComment);

        self::assertStringEqualsStringIgnoringLineEndings(<<<'EOT'
/**
 * This is a description
 *
 * @template Foo This is a generic description
 */
EOT, '' . new TsDocComment(
            description: 'This is a description',
            generics: [
                new TsGeneric(
                    name: 'Foo',
                    description: 'This is a generic description',
                ),
            ],
        ));

        self::assertStringEqualsStringIgnoringLineEndings(<<<'EOT'
/**
 * @deprecated This is a deprecation
 *
 * @template Foo This is a generic description
 */
EOT, '' . new TsDocComment(
            deprecation: 'This is a deprecation',
            generics: [
                new TsGeneric(
                    name: 'Foo',
                    description: 'This is a generic description',
                ),
            ],
        ));

        self::assertStringEqualsStringIgnoringLineEndings(<<<'EOT'
/**
 * This is a description
 *
 * @deprecated This is a deprecation
 */
EOT, '' . new TsDocComment(
            description: 'This is a description',
            deprecation: 'This is a deprecation',
        ));

        self::assertStringEqualsStringIgnoringLineEndings(<<<'EOT'
/**
 * @deprecated
 */
EOT, '' . new TsDocComment(
            deprecation: true,
        ));

        self::assertStringEqualsStringIgnoringLineEndings(<<<'EOT'
/**
 * @template Foo This is a generic description
 */
EOT, '' . new TsDocComment(
            generics: [
                new TsGeneric(
                    name: 'Foo',
                    description: 'This is a generic description',
                ),
            ],
        ));

        self::assertStringEqualsStringIgnoringLineEndings(<<<'EOT'
/**
 *
 */
EOT, '' . new TsDocComment(
            description: ' ',
        ));

        self::assertStringEqualsStringIgnoringLineEndings(<<<EOT
\t\t\t/**
\t\t\t *
\t\t\t */
EOT, (new TsDocComment(' '))->toString(new Indent(Indent::STYLE_TAB, 3)));

        self::assertStringEqualsStringIgnoringLineEndings('', '' . new TsDocComment());
    }
}
