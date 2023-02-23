<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\CamelCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\KebabCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\LowerCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\PascalCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\SnakeCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\UpperCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(className: CamelCase::class)]
#[CoversClass(className: KebabCase::class)]
#[CoversClass(className: LowerCase::class)]
#[CoversClass(className: PascalCase::class)]
#[CoversClass(className: SnakeCase::class)]
#[CoversClass(className: UpperCase::class)]
final class FileNameStrategyTest extends TestCase
{
    /**
     * @param class-string<FileNameStrategy> $fileNameStrategy
     */
    #[DataProvider(methodName: 'fileNameStrategyProvider')]
    public function testGetName(string $input, string $expected, string $fileNameStrategy): void
    {
        self::assertSame($expected, (new $fileNameStrategy())->getName($input));
    }

    /**
     * @return list<list{
     *     string,
     *     string,
     *     class-string<FileNameStrategy>,
     * }>
     */
    public static function fileNameStrategyProvider(): array
    {
        return [
            ['this Is a camel_case-string', 'thisIsACamelCaseString', CamelCase::class],
            ['this Is a kebab_case-string', 'this-is-a-kebab-case-string', KebabCase::class],
            ['this Is a lower_case-string', 'this is a lower_case-string', LowerCase::class],
            ['this Is a pascal_case-string', 'ThisIsAPascalCaseString', PascalCase::class],
            ['this Is a snake_case-string', 'this_is_a_snake_case_string', SnakeCase::class],
            ['this Is a upper_case-string', 'THIS IS A UPPER_CASE-STRING', UpperCase::class],
        ];
    }
}
