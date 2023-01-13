<?php

namespace Brainshaker95\PhpToTsBundle\Service;

use Brainshaker95\PhpToTsBundle\Interface\Config;
use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\FullConfig;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Contracts\Service\Attribute\Required;

class Dumper
{
    #[Required]
    public Filesystem $filesystem;

    #[Required]
    public Visitor $visitor;

    private FullConfig $config;

    private Parser $parser;

    /**
     * @param array{
     *     input_dir: string,
     *     output_dir: string,
     *     file_type: FileType::TYPE_*,
     *     indent: array{
     *         style: Indent::STYLE_*,
     *         count: int,
     *     },
     *     sort_strategies: class-string<SortStrategy>[],
     *     file_name_strategy: class-string<FileNameStrategy>
     * } $config
     */
    public function __construct(array $config)
    {
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);

        $this->config = new FullConfig(
            inputDir: $config['input_dir'],
            outputDir: $config['output_dir'],
            fileType: $config['file_type'],
            indent: new Indent($config['indent']['style'], $config['indent']['count']),
            sortStrategies: $config['sort_strategies'],
            fileNameStrategy: $config['file_name_strategy'],
        );
    }

    /**
     * Recursively dumps all TypeScriptables in the given directory.
     * - When no config is given the global bundle config will be used.
     *
     * @param Config|string|null $configOrDir directory to dump or config used for dumping
     * @param ?Config $config config used for dumping
     *
     * @throws Error
     * @throws FileNotFoundException
     */
    public function dumpDir(
        Config|string|null $configOrDir = null,
        ?Config $config = null,
    ): void {
        $config = $this->getConfig($configOrDir instanceof Config ? $configOrDir : $config);

        $dir = $this->filesystem->makeAbsolute(
            is_string($configOrDir) ? $configOrDir : $config->getInputDir(),
        );

        $this->filesystem->assertDir($dir);
        $this->dumpFiles([$dir], $config);
    }

    /**
     * Dumps all TypeScriptables in the given files.
     * - When no config is given the global bundle config will be used.
     *
     * @param array<SplFileInfo|string> $files array of files to dump
     * @param ?Config $config config used for dumping
     *
     * @throws Error
     * @throws FileNotFoundException
     */
    public function dumpFiles(array $files, ?Config $config = null): void
    {
        foreach ($this->filesystem->getSplFileInfoArray($files) as $file) {
            if ($file->isDir()) {
                $this->dumpFiles([...(new Finder())->in($file->getPathname())], $config);
            } else {
                $this->dumpFile($file, $config);
            }
        }
    }

    /**
     * Dumps all TypeScriptables in the given file.
     * - When no config is given the global bundle config will be used.
     *
     * @param SplFileInfo|string $file file to dump
     * @param ?Config $config config used for dumping
     *
     * @throws Error
     * @throws FileNotFoundException
     */
    public function dumpFile(SplFileInfo|string $file, ?Config $config = null): void
    {
        $tsInterfaces = $this->getTsInterfacesFromFile($file);

        if (!$tsInterfaces) {
            return;
        }

        $config     = $this->getConfig($config);
        $fileType   = $config->getFileType();
        $pathPrefix = $config->getOutputDir() . DIRECTORY_SEPARATOR;

        foreach ($tsInterfaces as $tsInterface) {
            $path = $this->filesystem->makeAbsolute($pathPrefix . $tsInterface->getFileName($fileType));

            $this->filesystem->dumpFile($path, $tsInterface->toString(
                fileType: $fileType,
                indent: $config->getIndent(),
                sortStrategies: $config->getSortStrategies(),
            ));
        }
    }

    /**
     * Creates TsInterface instances from all classes in the given file.
     *
     * @return TsInterface[]
     *
     * @throws FileNotFoundException
     * @throws Error
     */
    public function getTsInterfacesFromFile(SplFileInfo|string $file): ?array
    {
        $file = $this->filesystem->getSplFileInfo($file);

        $this->filesystem->assertFile($file->getRealPath());

        if (Str::toLower($file->getExtension()) !== 'php') {
            return null;
        }

        $statements = $this->parser->parse($file->getContents());

        if (!is_array($statements)) {
            return null;
        }

        $traverser = new NodeTraverser();

        $traverser->addVisitor($this->visitor);
        $traverser->traverse($statements);

        return $this->visitor->getTsInterfaces();
    }

    private function getConfig(?Config $config = null): FullConfig
    {
        if (!$config) {
            return $this->config;
        }

        if ($config instanceof FullConfig) {
            return $config;
        }

        return new FullConfig(
            inputDir: $config->getInputDir() ?? $this->config->getInputDir(),
            outputDir: $config->getOutputDir() ?? $this->config->getOutputDir(),
            fileType: $config->getFileType() ?? $this->config->getFileType(),
            indent: $config->getIndent() ?? $this->config->getIndent(),
            sortStrategies: $config->getSortStrategies() ?? $this->config->getSortStrategies(),
            fileNameStrategy: $config->getFileNameStrategy() ?? $this->config->getFileNameStrategy(),
        );
    }
}
