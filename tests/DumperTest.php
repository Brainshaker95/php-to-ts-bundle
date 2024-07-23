<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprFalseNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprFloatNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprIntegerNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprNullNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprStringNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprTrueNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstFetchNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ArrayShapeItemNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ArrayShapeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ArrayTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\ConstTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\GenericTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\IdentifierTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\IntersectionTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\NullableTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Ast\Type\UnionTypeNode;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\PascalCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\SnakeCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\FullConfig;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\PartialConfig;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\AlphabeticalDesc;
use Brainshaker95\PhpToTsBundle\Model\Config\TypeDefinitionType;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasIndent;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasQuotes;
use Brainshaker95\PhpToTsBundle\Model\TsDocComment;
use Brainshaker95\PhpToTsBundle\Model\TsEnum;
use Brainshaker95\PhpToTsBundle\Model\TsGeneric;
use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Brainshaker95\PhpToTsBundle\Service\Configuration;
use Brainshaker95\PhpToTsBundle\Service\Dumper;
use Brainshaker95\PhpToTsBundle\Service\Filesystem;
use Brainshaker95\PhpToTsBundle\Service\Visitor;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Small;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

use const PHP_EOL;

use function count;

/**
 * @internal
 */
#[Small]
#[CoversClass(ArrayShapeItemNode::class)]
#[CoversClass(ArrayShapeNode::class)]
#[CoversClass(ArrayTypeNode::class)]
#[CoversClass(ConstExprFalseNode::class)]
#[CoversClass(ConstExprFloatNode::class)]
#[CoversClass(ConstExprIntegerNode::class)]
#[CoversClass(ConstExprNullNode::class)]
#[CoversClass(ConstExprStringNode::class)]
#[CoversClass(ConstExprTrueNode::class)]
#[CoversClass(ConstFetchNode::class)]
#[CoversClass(ConstTypeNode::class)]
#[CoversClass(Dumper::class)]
#[CoversClass(GenericTypeNode::class)]
#[CoversClass(IdentifierTypeNode::class)]
#[CoversClass(IntersectionTypeNode::class)]
#[CoversClass(NullableTypeNode::class)]
#[CoversClass(TsDocComment::class)]
#[CoversClass(TsGeneric::class)]
#[CoversClass(TsInterface::class)]
#[CoversClass(TsProperty::class)]
#[CoversClass(TsEnum::class)]
#[CoversClass(UnionTypeNode::class)]
#[CoversClass(Visitor::class)]
#[CoversTrait(HasIndent::class)]
#[CoversTrait(HasQuotes::class)]
#[CoversClass(Converter::class)]
#[CoversClass(PhpStan::class)]
#[CoversClass(Quotes::class)]
#[CoversClass(Indent::class)]
#[CoversClass(FullConfig::class)]
#[CoversClass(PartialConfig::class)]
final class DumperTest extends KernelTestCase
{
    private Dumper $dumper;

    private Filesystem $filesystem;

    private FullConfig $config;

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
        $this->config     = $config->get();
        $this->inputDir   = $this->config->getInputDir();
        $this->outputDir  = $this->config->getOutputDir();
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->outputDir);

        parent::tearDown();
    }

    public function testDumpDirWithDefaultOptions(): void
    {
        $this->dumper->dumpDir(
            successCallback: $this->successCallback(...),
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
            successCallback: $this->successCallback(...),
        );
    }

    public function testDumpDirWithSomeOptionsChanged(): void
    {
        $this->dumper->dumpDir(
            config: new PartialConfig(
                indent: new Indent(count: 3),
                fileNameStrategy: PascalCase::class,
            ),
            successCallback: $this->successCallback(...),
        );
    }

    public function testDumpDirWithInputDirChanged(): void
    {
        $this->dumper->dumpDir(
            configOrDir: $this->inputDir . '/SubDir',
            successCallback: $this->successCallback(...),
        );
    }

    public function testDumpDirThrowingFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->dumper->dumpDir('does-not-exist');
    }

    public function testDumpFilesWithDefaultOptions(): void
    {
        $this->dumper->dumpFiles(
            files: [
                $this->inputDir . '/IterableTypes.php',
                $this->inputDir . '/SubDir/GenericTypes.php',
            ],
            successCallback: $this->successCallback(...),
        );
    }

    public function testDumpFilesWithADirectoryAsInput(): void
    {
        $paths = [];

        $this->dumper->dumpFiles(
            files: [
                $this->inputDir,
                $this->inputDir . '/SubDir/GenericTypes.php',
            ],
            successCallback: $this->successCallback(...),
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
            successCallback: $this->successCallback(...),
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
            successCallback: $this->successCallback(...),
        );
    }

    public function testDumpFilesThrowingFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->dumper->dumpFiles(['does-not-exist']);
    }

    public function testDumpFileWithDefaultOptions(): void
    {
        $this->dumper->dumpFile(
            file: $this->inputDir . '/IterableTypes.php',
            successCallback: $this->successCallback(...),
        );
    }

    public function testDumpFileWithAllOptionsChanged(): void
    {
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
            successCallback: $this->successCallback(...),
        );
    }

    public function testDumpFileWithSomeOptionsChanged(): void
    {
        $this->dumper->dumpFile(
            file: $this->inputDir . '/NativeTypes.php',
            config: new PartialConfig(
                inputDir: $this->inputDir . '/does-not-exist-and-should-be-ignored',
                indent: new Indent(count: 3),
                fileNameStrategy: PascalCase::class,
            ),
            successCallback: $this->successCallback(...),
        );
    }

    public function testDumpFileThrowingFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->dumper->dumpFile('does-not-exist');
    }

    public function testGetTsInterfacesFromFile(): void
    {
        $tsInterfaces = $this->dumper->getTsInterfacesFromFile($this->inputDir . '/SubDir/GenericTypes.php');

        self::assertTrue(count($tsInterfaces) === 1, 'Expected only one class in file.');

        $tsInterfaces[0]->config = $this->config;

        self::assertStringEqualsStringIgnoringLineEndings(
            expected: $this->filesystem->getContent('tests/Fixture/Output/generic-types.ts'),
            actual: $tsInterfaces[0]->toString() . PHP_EOL,
        );
    }

    public function testNoTsInterfaceInFile(): void
    {
        $this->dumper->dumpFile(
            file: __DIR__ . '/TestKernel.php',
            successCallback: static fn () => self::assertTrue(false, 'This file should not contain a TsInterface'),
        );

        $tsInterfaces = $this->dumper->getTsInterfacesFromFile(__DIR__ . '/assets/coverage.css');

        self::assertCount(0, $tsInterfaces);
    }

    private function successCallback(string $path): void
    {
        $file = $this->filesystem->getSplFileInfo($path);
        $name = $file->getFilename();

        self::assertStringEqualsStringIgnoringLineEndings(
            expected: $this->filesystem->getContent('tests/Fixture/Output/' . $name),
            actual: $file->getContents(),
        );
    }
}
