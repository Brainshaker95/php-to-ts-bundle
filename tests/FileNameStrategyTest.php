<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\CamelCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\KebabCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\LowerCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\PascalCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\SnakeCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\UpperCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy
 */
final class FileNameStrategyTest extends TestCase
{
    public function testCamelCase(): void
    {
        self::assertSame('thisIsACamelCaseString', (new CamelCase())->getName('this Is a camel_case-string'));
    }

    public function testKebabCase(): void
    {
        self::assertSame('this-is-a-kebab-case-string', (new KebabCase())->getName('this Is a kebab_case-string'));
    }

    public function testLowerCase(): void
    {
        self::assertSame('this is a lower_case-string', (new LowerCase())->getName('this Is a lower_case-string'));
    }

    public function testPascalCase(): void
    {
        self::assertSame('ThisIsAPascalCaseString', (new PascalCase())->getName('this Is a pascal_case-string'));
    }

    public function testSnakeCase(): void
    {
        self::assertSame('this_is_a_snake_case_string', (new SnakeCase())->getName('this Is a snake_case-string'));
    }

    public function testUpperCase(): void
    {
        self::assertSame('THIS IS A UPPER_CASE-STRING', (new UpperCase())->getName('this Is a upper_case-string'));
    }
}
