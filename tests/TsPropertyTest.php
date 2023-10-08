<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprFloatNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprIntegerNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprNullNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprStringNode;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\PartialConfig;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @small
 *
 * @covers \Brainshaker95\PhpToTsBundle\Model\TsProperty
 *
 * @PhpCsFixerIgnore heredoc_indentation
 */
final class TsPropertyTest extends TestCase
{
    public function testToString(): void
    {
        $tsProperty = new TsProperty(
            name: 'propertyName',
            type: TsProperty::TYPE_UNKNOWN,
            isReadonly: true,
            description: 'This is a description',
            deprecation: 'This is a deprecation',
        );

        self::assertSame($tsProperty->toString(), $tsProperty->__toString());

        self::assertStringEqualsStringIgnoringLineEndings(<<<'EOT'
  /**
   * This is a description
   *
   * @deprecated This is a deprecation
   */
  readonly propertyName: unknown;
EOT, '' . $tsProperty);

        self::assertStringEqualsStringIgnoringLineEndings(<<<'EOT'
  /**
   * This is a description
   */
  propertyName: 3.14;
EOT, '' . new TsProperty(
            name: 'propertyName',
            type: new ConstExprFloatNode('3.14'),
            description: 'This is a description',
        ));

        self::assertStringEqualsStringIgnoringLineEndings(<<<'EOT'
  /**
   * @deprecated This is a deprecation
   */
  propertyName: null;
EOT, '' . new TsProperty(
            name: 'propertyName',
            type: new ConstExprNullNode(),
            deprecation: 'This is a deprecation',
        ));

        self::assertStringEqualsStringIgnoringLineEndings(<<<'EOT'
  /**
   * @deprecated
   */
  propertyName: 69;
EOT, '' . new TsProperty(
            name: 'propertyName',
            type: new ConstExprIntegerNode('69'),
            deprecation: true,
        ));

        self::assertStringEqualsStringIgnoringLineEndings(<<<EOT
\t\t\tpropertyName: "literal-string";
EOT, '' . new TsProperty(
            name: 'propertyName',
            type: new ConstExprStringNode('literal-string'),
            config: new PartialConfig(
                indent: new Indent(Indent::STYLE_TAB, 3),
                quotes: new Quotes(Quotes::STYLE_DOUBLE),
            ),
        ));
    }
}
