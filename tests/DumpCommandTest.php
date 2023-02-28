<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Command\DumpCommand;
use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\PascalCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\SnakeCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\AlphabeticalDesc;
use Brainshaker95\PhpToTsBundle\Model\Config\TypeDefinitionType;
use Brainshaker95\PhpToTsBundle\Service\Configuration;
use Brainshaker95\PhpToTsBundle\Service\Filesystem;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;

use function array_merge;
use function sprintf;

/**
 * @internal
 *
 * @covers \Brainshaker95\PhpToTsBundle\Command\DumpDirCommand
 * @covers \Brainshaker95\PhpToTsBundle\Command\DumpFileCommand
 * @covers \Brainshaker95\PhpToTsBundle\Command\DumpFilesCommand
 */
final class DumpCommandTest extends KernelTestCase
{
    private Filesystem $filesystem;

    private string $inputDir;

    private string $outputDir;

    protected function setUp(): void
    {
        $container  = self::getContainer();
        $filesystem = $container->get(Filesystem::class);
        $config     = $container->get(Configuration::class);

        self::assertInstanceOf(Filesystem::class, $filesystem);
        self::assertInstanceOf(Configuration::class, $config);

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
        $this->assertCommandSuccess(
            code: self::runCommand('phptots:dump:dir'),
            outputDir: $this->outputDir,
            expectedFileCount: 3,
        );
    }

    public function testDumpDirWithAllOptionsChanged(): void
    {
        $this->assertCommandSuccess(
            code: self::runCommand('phptots:dump:dir', [
                Str::toKebab(C::INPUT_DIR_KEY)                     => $this->inputDir,
                '--' . Str::toKebab(C::OUTPUT_DIR_KEY)             => $this->outputDir . '/SubDir',
                '--' . Str::toKebab(C::FILE_TYPE_KEY)              => FileType::TYPE_DECLARATION,
                '--' . Str::toKebab(C::TYPE_DEFINITION_TYPE_KEY)   => TypeDefinitionType::TYPE_TYPE_ALIAS,
                '--' . Str::toKebab(DumpCommand::INDENT_STYLE_KEY) => Indent::STYLE_TAB,
                '--' . Str::toKebab(DumpCommand::INDENT_COUNT_KEY) => 3,
                '--' . Str::toKebab(C::QUOTES_KEY)                 => Quotes::STYLE_DOUBLE,
                '--' . Str::toKebab(C::SORT_STRATEGIES_KEY)        => [AlphabeticalDesc::class],
                '--' . Str::toKebab(C::FILE_NAME_STRATEGY_KEY)     => SnakeCase::class,
            ]),
            outputDir: $this->outputDir . '/SubDir',
            expectedFileCount: 3,
        );
    }

    public function testDumpDirWithSomeOptionsChanged(): void
    {
        $this->assertCommandSuccess(
            code: self::runCommand('phptots:dump:dir', [
                '--' . Str::toKebab(DumpCommand::INDENT_COUNT_KEY) => 3,
                '--' . Str::toKebab(C::FILE_NAME_STRATEGY_KEY)     => PascalCase::class,
            ]),
            outputDir: $this->outputDir,
            expectedFileCount: 3,
        );
    }

    public function testDumpDirWithInputDirChanged(): void
    {
        $this->assertCommandSuccess(
            code: self::runCommand('phptots:dump:dir', [
                Str::toKebab(C::INPUT_DIR_KEY) => $this->inputDir . '/SubDir',
            ]),
            outputDir: $this->outputDir,
            expectedFileCount: 1,
        );
    }

    public function testDumpDirThrowingFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);

        self::assertCommandFailure(self::runCommand('phptots:dump:dir', [
            Str::toKebab(C::INPUT_DIR_KEY) => 'does-not-exist',
        ]));
    }

    public function testDumpFilesWithDefaultOptions(): void
    {
        $this->assertCommandSuccess(
            code: self::runCommand('phptots:dump:files', [
                'input-files' => [
                    $this->inputDir . '/IterableTypes.php',
                    $this->inputDir . '/SubDir/GenericTypes.php',
                ],
            ]),
            outputDir: $this->outputDir,
            expectedFileCount: 2,
        );
    }

    public function testDumpFilesWithADirectoryAsInput(): void
    {
        $this->assertCommandSuccess(
            code: self::runCommand('phptots:dump:files', [
                'input-files' => [
                    $this->inputDir,
                    $this->inputDir . '/SubDir/GenericTypes.php',
                ],
            ]),
            outputDir: $this->outputDir,
            expectedFileCount: 3,
        );
    }

    public function testDumpFilesWithAllOptionsChanged(): void
    {
        $this->assertCommandSuccess(
            code: self::runCommand('phptots:dump:files', [
                'input-files'                                      => [$this->inputDir],
                '--' . Str::toKebab(C::OUTPUT_DIR_KEY)             => $this->outputDir . '/SubDir',
                '--' . Str::toKebab(C::FILE_TYPE_KEY)              => FileType::TYPE_DECLARATION,
                '--' . Str::toKebab(C::TYPE_DEFINITION_TYPE_KEY)   => TypeDefinitionType::TYPE_TYPE_ALIAS,
                '--' . Str::toKebab(DumpCommand::INDENT_STYLE_KEY) => Indent::STYLE_TAB,
                '--' . Str::toKebab(DumpCommand::INDENT_COUNT_KEY) => 3,
                '--' . Str::toKebab(C::QUOTES_KEY)                 => Quotes::STYLE_DOUBLE,
                '--' . Str::toKebab(C::SORT_STRATEGIES_KEY)        => [AlphabeticalDesc::class],
                '--' . Str::toKebab(C::FILE_NAME_STRATEGY_KEY)     => SnakeCase::class,
            ]),
            outputDir: $this->outputDir . '/SubDir',
            expectedFileCount: 3,
        );
    }

    public function testDumpFilesWithSomeOptionsChanged(): void
    {
        $this->assertCommandSuccess(
            code: self::runCommand('phptots:dump:files', [
                'input-files'                                      => [$this->inputDir],
                '--' . Str::toKebab(DumpCommand::INDENT_COUNT_KEY) => 3,
                '--' . Str::toKebab(C::FILE_NAME_STRATEGY_KEY)     => PascalCase::class,
            ]),
            outputDir: $this->outputDir,
            expectedFileCount: 3,
        );
    }

    public function testDumpFilesThrowingFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);

        self::assertCommandFailure(self::runCommand('phptots:dump:files', [
            'input-files' => ['does-not-exist'],
        ]));
    }

    public function testDumpFileWithDefaultOptions(): void
    {
        $this->assertCommandSuccess(
            code: self::runCommand('phptots:dump:file', [
                'input-file' => $this->inputDir . '/IterableTypes.php',
            ]),
            outputDir: $this->outputDir,
            expectedFileCount: 1,
        );
    }

    public function testDumpFileWithAllOptionsChanged(): void
    {
        $this->assertCommandSuccess(
            code: self::runCommand('phptots:dump:file', [
                'input-file'                                       => $this->inputDir . '/SubDir/GenericTypes.php',
                '--' . Str::toKebab(C::OUTPUT_DIR_KEY)             => $this->outputDir . '/SubDir',
                '--' . Str::toKebab(C::FILE_TYPE_KEY)              => FileType::TYPE_DECLARATION,
                '--' . Str::toKebab(C::TYPE_DEFINITION_TYPE_KEY)   => TypeDefinitionType::TYPE_TYPE_ALIAS,
                '--' . Str::toKebab(DumpCommand::INDENT_STYLE_KEY) => Indent::STYLE_TAB,
                '--' . Str::toKebab(DumpCommand::INDENT_COUNT_KEY) => 3,
                '--' . Str::toKebab(C::QUOTES_KEY)                 => Quotes::STYLE_DOUBLE,
                '--' . Str::toKebab(C::SORT_STRATEGIES_KEY)        => [AlphabeticalDesc::class],
                '--' . Str::toKebab(C::FILE_NAME_STRATEGY_KEY)     => SnakeCase::class,
            ]),
            outputDir: $this->outputDir . '/SubDir',
            expectedFileCount: 1,
        );
    }

    public function testDumpFileWithSomeOptionsChanged(): void
    {
        $this->assertCommandSuccess(
            code: self::runCommand('phptots:dump:file', [
                'input-file'                                       => $this->inputDir . '/NativeTypes.php',
                '--' . Str::toKebab(DumpCommand::INDENT_COUNT_KEY) => 3,
                '--' . Str::toKebab(C::FILE_NAME_STRATEGY_KEY)     => PascalCase::class,
            ]),
            outputDir: $this->outputDir,
            expectedFileCount: 1,
        );
    }

    public function testDumpFileThrowingFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);

        self::assertCommandFailure(self::runCommand('phptots:dump:file', [
            'input-file' => 'does-not-exist',
        ]));
    }

    private function assertCommandSuccess(int $code, string $outputDir, int $expectedFileCount): void
    {
        self::assertTrue($code === Command::SUCCESS, sprintf('Expected exit code %s.', Command::SUCCESS));

        $files = (new Finder())->files()->in($outputDir);

        self::assertTrue(
            $files->count() === $expectedFileCount,
            sprintf('Expected %s dumped file%s.', $expectedFileCount, $expectedFileCount === 1 ? '' : 's'),
        );

        foreach ($files as $file) {
            $name = $file->getFilename();

            self::assertStringEqualsStringIgnoringLineEndings(
                expected: $this->filesystem->getContent('tests/Fixture/Output/' . $name),
                actual: $this->filesystem->getContent($outputDir . '/' . $name),
            );
        }
    }

    private static function assertCommandFailure(int $code): void
    {
        self::assertTrue($code === Command::FAILURE, sprintf('Expected exit code %s.', Command::FAILURE));
    }

    /**
     * @param array<int|string|string[]> $arguments
     */
    private static function runCommand(string $command, array $arguments = []): int
    {
        $application = new Application(self::$kernel);

        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        return $application->run(
            new ArrayInput(array_merge([
                'command' => $command,
            ], $arguments)),
            new NullOutput(),
        );
    }
}
