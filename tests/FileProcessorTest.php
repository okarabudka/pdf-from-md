<?php

declare(strict_types=1);

namespace Okarabudka\PdfFromMd\Tests;

use Okarabudka\PdfFromMd\FileProcessor;
use PHPUnit\Framework\TestCase;

final class FileProcessorTest extends TestCase
{
    private const string FIXTURES = __DIR__ . '/mock-files';

    public function testListsFilesOfTheRootFolderInIndexOrder(): void
    {
        $files = (new FileProcessor(self::FIXTURES))->getSortedListOfFiles();

        self::assertSame(['1.md', '2.md', '3.md'], array_map('basename', $files));
    }

    public function testListsChapterFilesChapterByChapter(): void
    {
        $files = (new FileProcessor(self::FIXTURES, 'md', true))->getSortedListOfFiles();

        self::assertCount(6, $files);
        self::assertStringEndsWith('1/1.md', $files[0]);
        self::assertStringEndsWith('2/3.md', $files[5]);
    }

    public function testSortsByLeadingNumberNotByAllDigitsInThePath(): void
    {
        $dir = sys_get_temp_dir() . '/pdf-from-md-' . bin2hex(random_bytes(4));
        mkdir($dir);
        foreach (['10-outro.md', '2-body-v3.md', '1-intro.md'] as $name) {
            touch($dir . '/' . $name);
        }

        try {
            $files = (new FileProcessor($dir))->getSortedListOfFiles();
            self::assertSame(['1-intro.md', '2-body-v3.md', '10-outro.md'], array_map('basename', $files));
        } finally {
            array_map('unlink', glob($dir . '/*.md') ?: []);
            rmdir($dir);
        }
    }

    public function testEscapesTheOutputPathAndEveryInputFile(): void
    {
        $command = (new FileProcessor(self::FIXTURES))->buildCommand(["a b.md", "x'; rm -rf /.md"], 'my book.pdf');

        self::assertStringContainsString("-o '" . self::FIXTURES . "/my book.pdf'", $command);
        self::assertStringContainsString("'a b.md'", $command);
        self::assertStringContainsString("'x'\\''; rm -rf /.md'", $command);
    }

    public function testRejectsAnEmptyFileList(): void
    {
        $this->expectException(\RuntimeException::class);

        (new FileProcessor(self::FIXTURES))->buildCommand([]);
    }

    public function testRejectsAMissingDirectory(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new FileProcessor(self::FIXTURES . '/missing');
    }
}
