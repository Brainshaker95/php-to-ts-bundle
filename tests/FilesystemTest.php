<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Service\Filesystem;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 *
 * @small
 *
 * @covers \Brainshaker95\PhpToTsBundle\Service\Filesystem
 *
 * @PhpCsFixerIgnore heredoc_indentation
 */
final class FilesystemTest extends KernelTestCase
{
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $container  = self::getContainer();
        $filesystem = $container->get(Filesystem::class);

        self::assertInstanceOf(Filesystem::class, $filesystem);

        $this->filesystem = $filesystem;
    }

    public function testGetSplFileInfoArray(): void
    {
        $files = $this->filesystem->getSplFileInfoArray(['LICENSE', 'README.md']);

        foreach ($files as $file) {
            self::assertInstanceOf(SplFileInfo::class, $file);
            self::assertSame($file, $this->filesystem->getSplFileInfo($file));
        }
    }

    public function testGetSplFileInfo(): void
    {
        $file = $this->filesystem->getSplFileInfo('LICENSE');

        self::assertInstanceOf(SplFileInfo::class, $file);
        self::assertSame($file, $this->filesystem->getSplFileInfo($file));
    }

    public function testGetContent(): void
    {
        $license = $this->filesystem->getContent('LICENSE');

        self::assertSame(<<<'EOT'
MIT License

Copyright (c) 2023 Patrick Rupp

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

EOT, $license);
    }

    public function testMakeAbsolute(): void
    {
        self::assertTrue($this->filesystem->isAbsolutePath(
            $this->filesystem->makeAbsolute('/some/path'),
        ));

        self::assertTrue($this->filesystem->isAbsolutePath(
            $this->filesystem->makeAbsolute('some/path'),
        ));

        self::assertTrue($this->filesystem->isAbsolutePath(
            $this->filesystem->makeAbsolute('../some/path'),
        ));

        self::assertTrue($this->filesystem->isAbsolutePath(
            $this->filesystem->makeAbsolute('../../some/../path'),
        ));
    }

    public function testAssertFd(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->filesystem->assertFd('does/not/exist');
    }

    public function testAssertFile(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->filesystem->assertFile('tests');
    }

    public function testAssertDir(): void
    {
        $this->expectException(FileNotFoundException::class);

        /**
         * @phpstan-ignore-next-line
         */
        $this->filesystem->assertDir('tests/TestKernel.php');
    }
}
