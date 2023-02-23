<?php

declare(strict_types=1);

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

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

use function is_array;
use function is_string;

final class Dumper
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
     * Dumps all TypeScriptables in the given directory.
     * - When no config is given the global bundle config will be used.
     *
     * @param Config|string|null $configOrDir directory to dump or config used for dumping
     * @param ?Config $config config used for dumping
     * @param ?callable(string $path, TsInterface $tsInterface): void $successCallback callback to run for dumped file
     *
     * @throws FileNotFoundException
     */
    public function dumpDir(
        Config|string|null $configOrDir = null,
        ?Config $config = null,
        ?callable $successCallback = null,
    ): void {
        $config = $this->config->merge($configOrDir instanceof Config ? $configOrDir : $config);

        $dir = $this->filesystem->makeAbsolute(
            is_string($configOrDir) ? $configOrDir : $config->getInputDir(),
        );

        $this->filesystem->assertDir($dir);
        $this->dumpFiles([$dir], $config, $successCallback);
    }

    /**
     * Dumps all TypeScriptables in the given files and directories.
     * - When no config is given the global bundle config will be used.
     *
     * @param array<SplFileInfo|string> $files array of files to dump
     * @param ?Config $config config used for dumping
     * @param ?callable(string $path, TsInterface $tsInterface): void $successCallback callback to run for dumped file
     *
     * @throws FileNotFoundException
     */
    public function dumpFiles(
        array $files,
        ?Config $config = null,
        ?callable $successCallback = null,
    ): void {
        foreach ($this->filesystem->getSplFileInfoArray($files) as $file) {
            if ($file->isDir()) {
                $this->dumpFiles([...(new Finder())->depth(0)->in($file->getPathname())], $config, $successCallback);
            } else {
                $this->dumpFile($file, $config, $successCallback);
            }
        }
    }

    /**
     * Dumps all TypeScriptables in the given file.
     * - When no config is given the global bundle config will be used.
     *
     * @param SplFileInfo|string $file file to dump
     * @param ?Config $config config used for dumping
     * @param ?callable(string $path, TsInterface $tsInterface): void $successCallback callback to run for dumped file
     *
     * @throws FileNotFoundException
     */
    public function dumpFile(
        SplFileInfo|string $file,
        ?Config $config = null,
        ?callable $successCallback = null,
    ): void {
        $config       = $this->config->merge($config);
        $tsInterfaces = $this->getTsInterfacesFromFile($file, $config);

        if (!$tsInterfaces) {
            return;
        }

        $pathPrefix = $config->getOutputDir() . DIRECTORY_SEPARATOR;

        foreach ($tsInterfaces as $tsInterface) {
            $fileName = $tsInterface->getFileName();
            $path     = $this->filesystem->makeAbsolute($pathPrefix . $fileName);

            $this->filesystem->dumpFile($path, $tsInterface->toString() . PHP_EOL);

            if ($successCallback) {
                $successCallback($path, $tsInterface);
            }
        }
    }

    /**
     * Creates TsInterface instances from all classes in the given file.
     *
     * @param SplFileInfo|string $file file to extract TsInterface instances from
     *
     * @return TsInterface[]
     *
     * @throws FileNotFoundException
     */
    public function getTsInterfacesFromFile(SplFileInfo|string $file, ?Config $config = null): ?array
    {
        $file = $this->filesystem->getSplFileInfo($file);

        $this->filesystem->assertFile($file->getRealPath());

        if (Str::toLower($file->getExtension()) !== 'php') {
            return null;
        }

        $statements = null;

        try {
            $statements = $this->parser->parse($file->getContents());
        } catch (Error) {
            return null;
        }

        if (!is_array($statements)) {
            return null;
        }

        $this->visitor->config = $config;
        $traverser             = new NodeTraverser();

        $traverser->addVisitor($this->visitor);
        $traverser->traverse($statements);

        return $this->visitor->getTsInterfaces();
    }
}
