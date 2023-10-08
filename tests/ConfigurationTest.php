<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\PascalCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\FullConfig;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\PartialConfig;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\AlphabeticalAsc;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\ReadonlyLast;
use Brainshaker95\PhpToTsBundle\Model\Config\TypeDefinitionType;
use Brainshaker95\PhpToTsBundle\Service\Configuration;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function array_diff;
use function count;

/**
 * @internal
 *
 * @small
 *
 * @covers \Brainshaker95\PhpToTsBundle\DependencyInjection\Configuration
 * @covers \Brainshaker95\PhpToTsBundle\DependencyInjection\PhpToTsExtension
 * @covers \Brainshaker95\PhpToTsBundle\Model\Config\FullConfig
 * @covers \Brainshaker95\PhpToTsBundle\Model\Config\PartialConfig
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

    public function testThatConfigIsCorrectlyLoaded(): void
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

    public function testThatConfigCanBeMergedWithPartialConfig(): void
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

    public function testThatConfigIsReplacedWhemMergingFullConfig(): void
    {
        $config = $this->config->merge(new FullConfig(
            inputDir: C::INPUT_DIR_DEFAULT,
            outputDir: C::OUTPUT_DIR_DEFAULT,
            fileType: C::FILE_TYPE_DEFAULT,
            typeDefinitionType: C::TYPE_DEFINITION_TYPE_DEFAULT,
            indent: new Indent(),
            quotes: new Quotes(),
            sortStrategies: C::SORT_STRATEGIES_DEFAULT,
            fileNameStrategy: C::FILE_NAME_STRATEGY_DEFAULT,
        ));

        self::assertNotSame($this->config->get(), $config);
    }

    public function testThatConfigIsKepyWhenMergingNull(): void
    {
        $config = $this->config->merge(null);

        self::assertSame($this->config->get(), $config);
    }

    public function testConfigCreationFromArray(): void
    {
        $fullConfig     = FullConfig::fromArray($this->phpToTs);
        $partialConfig1 = PartialConfig::fromArray($this->phpToTs);
        $partialConfig2 = PartialConfig::fromArray([]);

        self::assertInstanceOf(FullConfig::class, $fullConfig);
        self::assertInstanceOf(PartialConfig::class, $partialConfig1);
        self::assertInstanceOf(PartialConfig::class, $partialConfig2);

        self::assertSame($this->phpToTs[C::INPUT_DIR_KEY], $partialConfig1->getInputDir());
        self::assertSame($this->phpToTs[C::OUTPUT_DIR_KEY], $partialConfig1->getOutputDir());
        self::assertSame($this->phpToTs[C::FILE_TYPE_KEY], $partialConfig1->getFileType());
        self::assertSame($this->phpToTs[C::TYPE_DEFINITION_TYPE_KEY], $partialConfig1->getTypeDefinitionType());
        self::assertSame($this->phpToTs[C::INDENT_KEY][C::INDENT_STYLE_KEY], $partialConfig1->getIndent()?->style);
        self::assertSame($this->phpToTs[C::INDENT_KEY][C::INDENT_COUNT_KEY], $partialConfig1->getIndent()->count);
        self::assertSame($this->phpToTs[C::QUOTES_KEY], $partialConfig1->getQuotes()?->style);
        self::assertSame($this->phpToTs[C::SORT_STRATEGIES_KEY], $partialConfig1->getSortStrategies());
        self::assertSame($this->phpToTs[C::FILE_NAME_STRATEGY_KEY], $partialConfig1->getFileNameStrategy());

        self::assertNull($partialConfig2->getInputDir());
        self::assertNull($partialConfig2->getOutputDir());
        self::assertNull($partialConfig2->getFileType());
        self::assertNull($partialConfig2->getTypeDefinitionType());
        self::assertNull($partialConfig2->getIndent());
        self::assertNull($partialConfig2->getIndent());
        self::assertNull($partialConfig2->getQuotes());
        self::assertNull($partialConfig2->getSortStrategies());
        self::assertNull($partialConfig2->getFileNameStrategy());
    }

    public function testSettingOfConfigValues(): void
    {
        $baseConfig = $this->config->get();

        foreach ([clone $baseConfig, new PartialConfig()] as $otherConfig) {
            $otherConfig
                ->setInputDir('Test')
                ->setOutputDir('Test')
                ->setFileType(FileType::TYPE_DECLARATION)
                ->setTypeDefinitionType(TypeDefinitionType::TYPE_TYPE_ALIAS)
                ->setIndent(new Indent(Indent::STYLE_TAB, 1))
                ->setQuotes(new Quotes(Quotes::STYLE_DOUBLE))
                ->setSortStrategies([AlphabeticalAsc::class])
                ->setFileNameStrategy(PascalCase::class)
            ;

            self::assertNotSame($baseConfig->getInputDir(), $otherConfig->getInputDir());
            self::assertNotSame($baseConfig->getOutputDir(), $otherConfig->getOutputDir());
            self::assertNotSame($baseConfig->getFileType(), $otherConfig->getFileType());
            self::assertNotSame($baseConfig->getTypeDefinitionType(), $otherConfig->getTypeDefinitionType());
            self::assertNotSame($baseConfig->getIndent(), $otherConfig->getIndent());
            self::assertNotSame($baseConfig->getQuotes(), $otherConfig->getQuotes());
            self::assertNotSame($baseConfig->getSortStrategies(), $otherConfig->getSortStrategies());
            self::assertNotSame($baseConfig->getFileNameStrategy(), $otherConfig->getFileNameStrategy());
        }
    }
}
