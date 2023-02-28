<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\PascalCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\SnakeCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\FullConfig;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\PartialConfig;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\AlphabeticalDesc;
use Brainshaker95\PhpToTsBundle\Model\Config\TypeDefinitionType;
use Brainshaker95\PhpToTsBundle\Service\Configuration;
use Brainshaker95\PhpToTsBundle\Service\Dumper;
use Brainshaker95\PhpToTsBundle\Service\Filesystem;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

use function array_key_exists;
use function sprintf;

/**
 * @internal
 *
 * @covers \Brainshaker95\PhpToTsBundle\Service\Dumper
 */
final class DumperTest extends KernelTestCase
{
    private const EXPECTED_OUTPUT_DIR = 'tests/Fixture/Output';

    private Dumper $dumper;

    private Filesystem $filesystem;

    private string $inputDir;

    private string $outputDir;

    protected function setUp(): void
    {
        $container  = self::getContainer();
        $dumper     = $container->get(Dumper::class);
        $filesystem = $container->get(Filesystem::class);
        $config     = $container->get(Configuration::class);

        self::assertInstanceOf(Dumper::class, $dumper);
        self::assertInstanceOf(Filesystem::class, $filesystem);
        self::assertInstanceOf(Configuration::class, $config);

        $this->dumper     = $dumper;
        $this->filesystem = $filesystem;
        $this->inputDir   = $config->get()->getInputDir();
        $this->outputDir  = $config->get()->getOutputDir();
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->outputDir);

        parent::tearDown();
    }

    public function testDumpDirWithDefaultOptions(): void
    {
        $this->dumper->dumpDir(
            successCallback: fn (string $path) => $this->successCallback($this->outputDir, $path),
        );
    }

    public function testDumpDirWithAllOptionsChanged(): void
    {
        $this->dumper->dumpDir(
            configOrDir: new FullConfig(
                inputDir: $this->inputDir,
                outputDir: $this->outputDir . '/SubDir',
                fileType: FileType::TYPE_DECLARATION,
                typeDefinitionType: TypeDefinitionType::TYPE_TYPE_ALIAS,
                indent: new Indent(Indent::STYLE_TAB, 3),
                quotes: new Quotes(Quotes::STYLE_DOUBLE),
                sortStrategies: [AlphabeticalDesc::class],
                fileNameStrategy: SnakeCase::class,
            ),
            successCallback: fn (string $path) => $this->successCallback($this->outputDir . '/SubDir', $path),
        );
    }

    public function testDumpDirWithSomeOptionsChanged(): void
    {
        $this->dumper->dumpDir(
            config: new PartialConfig(
                indent: new Indent(count: 3),
                fileNameStrategy: PascalCase::class,
            ),
            successCallback: fn (string $path) => $this->successCallback($this->outputDir, $path),
        );
    }

    public function testDumpDirWithInputDirChanged(): void
    {
        $fileCounter = 0;

        $this->dumper->dumpDir(
            configOrDir: $this->inputDir . '/SubDir',
            successCallback: function (string $path) use (&$fileCounter): void {
                $fileCounter += 1;

                self::assertTrue($fileCounter === 1, sprintf(
                    'The directory "%s" should only contain one class.',
                    $this->inputDir . '/SubDir',
                ));

                $this->successCallback($this->outputDir, $path);
            },
        );
    }

    public function testDumpDirThrowingFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->dumper->dumpDir('does-not-exist');
    }

    public function testDumpFilesWithDefaultOptions(): void
    {
        $fileCounter = 0;

        $this->dumper->dumpFiles(
            files: [
                $this->inputDir . '/IterableTypes.php',
                $this->inputDir . '/SubDir/GenericTypes.php',
            ],
            successCallback: function (string $path) use (&$fileCounter): void {
                $fileCounter += 1;

                self::assertTrue($fileCounter <= 2, 'Expected 2 dumped files.');
                $this->successCallback($this->outputDir, $path);
            },
        );
    }

    public function testDumpFilesWithADirectoryAsInput(): void
    {
        $fileCounter = 0;
        $paths       = [];

        $this->dumper->dumpFiles(
            files: [
                $this->inputDir,
                $this->inputDir . '/SubDir/GenericTypes.php',
            ],
            successCallback: function (string $path) use (&$fileCounter, &$paths): void {
                if (array_key_exists($path, $paths)) {
                    $fileCounter += 1;
                } else {
                    $paths[$path] = true;
                }

                self::assertTrue($fileCounter <= 3, 'Expected 3 dumped files.');
                $this->successCallback($this->outputDir, $path);
            },
        );
    }

    public function testDumpFilesWithAllOptionsChanged(): void
    {
        $this->dumper->dumpFiles(
            files: [$this->inputDir],
            config: new FullConfig(
                inputDir: $this->inputDir . '/does-not-exist-and-should-be-ignored',
                outputDir: $this->outputDir . '/SubDir',
                fileType: FileType::TYPE_DECLARATION,
                typeDefinitionType: TypeDefinitionType::TYPE_TYPE_ALIAS,
                indent: new Indent(Indent::STYLE_TAB, 3),
                quotes: new Quotes(Quotes::STYLE_DOUBLE),
                sortStrategies: [AlphabeticalDesc::class],
                fileNameStrategy: SnakeCase::class,
            ),
            successCallback: fn (string $path) => $this->successCallback($this->outputDir . '/SubDir', $path),
        );
    }

    public function testDumpFilesWithSomeOptionsChanged(): void
    {
        $this->dumper->dumpFiles(
            files: [$this->inputDir],
            config: new PartialConfig(
                inputDir: $this->inputDir . '/does-not-exist-and-should-be-ignored',
                indent: new Indent(count: 3),
                fileNameStrategy: PascalCase::class,
            ),
            successCallback: fn (string $path) => $this->successCallback($this->outputDir, $path),
        );
    }

    public function testDumpFilesThrowingFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->dumper->dumpFiles(['does-not-exist']);
    }

    public function testDumpFileWithDefaultOptions(): void
    {
        $fileCounter = 0;

        $this->dumper->dumpFile(
            file: $this->inputDir . '/IterableTypes.php',
            successCallback: function (string $path) use (&$fileCounter): void {
                $fileCounter += 1;

                self::assertTrue($fileCounter === 1, 'Expected 1 dumped file.');
                $this->successCallback($this->outputDir, $path);
            },
        );
    }

    public function testDumpFileWithAllOptionsChanged(): void
    {
        $fileCounter = 0;

        $this->dumper->dumpFile(
            file: $this->inputDir . '/SubDir/GenericTypes.php',
            config: new FullConfig(
                inputDir: $this->inputDir . '/does-not-exist-and-should-be-ignored',
                outputDir: $this->outputDir . '/SubDir',
                fileType: FileType::TYPE_DECLARATION,
                typeDefinitionType: TypeDefinitionType::TYPE_TYPE_ALIAS,
                indent: new Indent(Indent::STYLE_TAB, 3),
                quotes: new Quotes(Quotes::STYLE_DOUBLE),
                sortStrategies: [AlphabeticalDesc::class],
                fileNameStrategy: SnakeCase::class,
            ),
            successCallback: function (string $path) use (&$fileCounter): void {
                $fileCounter += 1;

                self::assertTrue($fileCounter === 1, 'Expected 1 dumped file.');
                $this->successCallback($this->outputDir . '/SubDir', $path);
            },
        );
    }

    public function testDumpFileWithSomeOptionsChanged(): void
    {
        $fileCounter = 0;

        $this->dumper->dumpFile(
            file: $this->inputDir . '/NativeTypes.php',
            config: new PartialConfig(
                inputDir: $this->inputDir . '/does-not-exist-and-should-be-ignored',
                indent: new Indent(count: 3),
                fileNameStrategy: PascalCase::class,
            ),
            successCallback: function (string $path) use (&$fileCounter): void {
                $fileCounter += 1;

                self::assertTrue($fileCounter === 1, 'Expected 1 dumped file.');
                $this->successCallback($this->outputDir, $path);
            },
        );
    }

    public function testDumpFileThrowingFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->dumper->dumpFile('does-not-exist');
    }

    private function successCallback(string $outputDir, string $path): void
    {
        $name = $this->filesystem->getSplFileInfo($path)->getFilename();

        self::assertStringEqualsStringIgnoringLineEndings(
            expected: $this->loadExpectedOutput($name),
            actual: $this->loadActualOutput($outputDir, $name),
        );
    }

    private function loadExpectedOutput(string $file): string
    {
        return $this->filesystem->getContent(self::EXPECTED_OUTPUT_DIR . '/' . $file);
    }

    private function loadActualOutput(string $outputDir, string $file): string
    {
        return $this->filesystem->getContent($outputDir . '/' . $file);
    }
}
