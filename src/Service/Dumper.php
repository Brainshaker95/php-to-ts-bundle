<?php

namespace Brainshaker95\PhpToTsBundle\Service;

use Brainshaker95\PhpToTsBundle\Interface\Config;
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
    public Configuration $config;

    #[Required]
    public Filesystem $filesystem;

    #[Required]
    public Visitor $visitor;

    private Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
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
        $config = $this->config->merge($configOrDir instanceof Config ? $configOrDir : $config);

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

        $config           = $this->config->merge($config);
        $fileType         = $config->getFileType();
        $fileNameStrategy = $config->getFileNameStrategy();
        $pathPrefix       = $config->getOutputDir() . DIRECTORY_SEPARATOR;

        foreach ($tsInterfaces as $tsInterface) {
            $fileName = $tsInterface->getFileName($fileType, $fileNameStrategy);
            $path     = $this->filesystem->makeAbsolute($pathPrefix . $fileName);

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
}
