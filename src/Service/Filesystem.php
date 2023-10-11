<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Service;

use RuntimeException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\SplFileInfo;

use function array_map;
use function is_dir;
use function is_file;
use function sprintf;

/**
 * @internal
 */
final class Filesystem extends SymfonyFilesystem
{
    public function __construct(
        private readonly string $projectDir,
    ) {}

    /**
     * Ensures an array of SplFileInfo objects.
     *
     * @param array<SplFileInfo|string> $files the files to ensure
     *                                         - If a single file `instanceof SplFileInfo`:
     *                                         appends it to the output array
     *                                         - If a single file `is_string`:
     *                                         tries to create a SplFileInfo object for the file descriptor
     *                                         at the given path and appends it to output the array
     *
     * @return SplFileInfo[]
     *
     * @throws FileNotFoundException
     */
    public function getSplFileInfoArray(array $files): array
    {
        return array_map($this->getSplFileInfo(...), $files);
    }

    /**
     * Ensures a SplFileInfo object.
     *
     * @param SplFileInfo|string $file the file to ensure
     *                                 - If file `instanceof SplFileInfo`:
     *                                 returns it
     *                                 - If file `is_string`:
     *                                 tries to create a SplFileInfo object for the file descriptor
     *                                 at the given path
     *
     * @throws FileNotFoundException
     */
    public function getSplFileInfo(SplFileInfo|string $file): SplFileInfo
    {
        if ($file instanceof SplFileInfo) {
            return $file;
        }

        $path = $this->makeAbsolute($file);

        $this->assertFd($path);

        $extension = Path::hasExtension($path) ? '.' . Path::getExtension($path) : '';
        $name      = Path::getFilenameWithoutExtension($path) . $extension;

        return new SplFileInfo($path, '', $name);
    }

    /**
     * Gets the content of a file.
     *
     * @throws FileNotFoundException
     * @throws RuntimeException
     */
    public function getContent(SplFileInfo|string $file): string
    {
        return $this->getSplFileInfo($file)->getContents();
    }

    /**
     * Turns a relative path into an absolute path in canonical form.
     *
     * @param string $path the path to convert
     */
    public function makeAbsolute(string $path): string
    {
        return Path::makeAbsolute($path, $this->projectDir);
    }

    /**
     * Asserts if the given file descriptor exists.
     *
     * @param string $fd the file descriptor to check
     *
     * @throws FileNotFoundException
     */
    public function assertFd(string $fd): void
    {
        if (!$this->exists($fd)) {
            throw new FileNotFoundException(sprintf(
                'File descriptor "%s" not found.',
                $fd,
            ));
        }
    }

    /**
     * Asserts if the given file exists.
     *
     * @param string $file the file to check
     *
     * @throws FileNotFoundException
     */
    public function assertFile(string $file): void
    {
        $this->assertFd($file);

        if (!is_file($file)) {
            throw new FileNotFoundException(sprintf(
                'File "%s" not found.',
                $file,
            ));
        }
    }

    /**
     * Asserts if the given directory exists.
     *
     * @param string $dir the directory to check
     *
     * @throws FileNotFoundException
     */
    public function assertDir(string $dir): void
    {
        $this->assertFd($dir);

        if (!is_dir($dir)) {
            throw new FileNotFoundException(sprintf(
                'Directoy "%s" not found.',
                $dir,
            ));
        }
    }
}
