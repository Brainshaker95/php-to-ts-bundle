<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Service\Configuration;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function array_diff;
use function count;

/**
 * @internal
 *
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
         * @var ConfigurationArray
         */
        $phpToTs = $container->getParameter('php_to_ts');

        self::assertInstanceOf(Configuration::class, $config);
        self::assertIsArray($phpToTs);

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
}
