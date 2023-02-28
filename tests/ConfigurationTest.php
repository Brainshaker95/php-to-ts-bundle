<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\PartialConfig;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\AlphabeticalAsc;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\ReadonlyLast;
use Brainshaker95\PhpToTsBundle\Service\Configuration;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function array_diff;
use function count;

/**
 * @internal
 *
 * @covers \Brainshaker95\PhpToTsBundle\DependencyInjection\Configuration
 * @covers \Brainshaker95\PhpToTsBundle\DependencyInjection\PhpToTsExtension
 * @covers \Brainshaker95\PhpToTsBundle\Service\Configuration
 *
 * @phpstan-import-type ConfigurationArray from Configuration
 */
final class ConfigurationTest extends KernelTestCase
{
    private Configuration $config;

    /**
     * @phpstan-var ConfigurationArray
     */
    private array $phpToTs;

    protected function setUp(): void
    {
        $container = self::getContainer();
        $config    = $container->get(Configuration::class);

        /**
         * @phpstan-var ConfigurationArray
         */
        $phpToTs = $container->getParameter('php_to_ts');

        self::assertInstanceOf(Configuration::class, $config);
        self::assertIsArray($phpToTs);

        self::assertSame(
            count($phpToTs),
            (new ReflectionClass($config->get()::class))->getConstructor()?->getNumberOfParameters(),
        );

        $this->config  = $config;
        $this->phpToTs = $phpToTs;
    }

    public function testThatTheConfigurationIsCorrectlyLoaded(): void
    {
        $config = $this->config->get();

        self::assertSame($this->phpToTs[C::INPUT_DIR_KEY], $config->getInputDir());
        self::assertSame($this->phpToTs[C::OUTPUT_DIR_KEY], $config->getOutputDir());
        self::assertSame($this->phpToTs[C::FILE_TYPE_KEY], $config->getFileType());
        self::assertSame($this->phpToTs[C::TYPE_DEFINITION_TYPE_KEY], $config->getTypeDefinitionType());
        self::assertSame($this->phpToTs[C::INDENT_KEY][C::INDENT_STYLE_KEY], $config->getIndent()->style);
        self::assertSame($this->phpToTs[C::INDENT_KEY][C::INDENT_COUNT_KEY], $config->getIndent()->count);
        self::assertSame($this->phpToTs[C::QUOTES_KEY], $config->getQuotes()->style);
        self::assertTrue(count(array_diff($this->phpToTs[C::SORT_STRATEGIES_KEY], $config->getSortStrategies())) === 0);
        self::assertSame($this->phpToTs[C::FILE_NAME_STRATEGY_KEY], $config->getFileNameStrategy());
    }

    public function testThatTheConfigurationCanBeMergedWithOther(): void
    {
        $fileType = FileType::TYPE_DECLARATION;
        $indent   = new Indent(Indent::STYLE_TAB);

        $sortStrategies = [
            AlphabeticalAsc::class,
            ReadonlyLast::class,
        ];

        $config = $this->config->merge(new PartialConfig(
            fileType: $fileType,
            indent: $indent,
            sortStrategies: $sortStrategies,
        ));

        self::assertSame($this->phpToTs[C::INPUT_DIR_KEY], $config->getInputDir());
        self::assertSame($this->phpToTs[C::OUTPUT_DIR_KEY], $config->getOutputDir());
        self::assertSame($fileType, $config->getFileType());
        self::assertSame($this->phpToTs[C::TYPE_DEFINITION_TYPE_KEY], $config->getTypeDefinitionType());
        self::assertSame($indent->style, $config->getIndent()->style);
        self::assertSame($this->phpToTs[C::INDENT_KEY][C::INDENT_COUNT_KEY], $config->getIndent()->count);
        self::assertSame($this->phpToTs[C::QUOTES_KEY], $config->getQuotes()->style);
        self::assertTrue(count(array_diff($sortStrategies, $config->getSortStrategies())) === 0);
        self::assertSame($this->phpToTs[C::FILE_NAME_STRATEGY_KEY], $config->getFileNameStrategy());
    }
}
