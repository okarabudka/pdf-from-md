<?php

declare(strict_types=1);

namespace Okarabudka\PdfFromMd;

final class FileProcessor
{
    private readonly string $rootPath;

    public function __construct(
        string $rootPath,
        private readonly string $fileExtension = 'md',
        private readonly bool $useChapterFolders = false,
    ) {
        if (!is_dir($rootPath)) {
            throw new \InvalidArgumentException(sprintf('Directory "%s" does not exist.', $rootPath));
        }

        $this->rootPath = rtrim($rootPath, '/\\') . '/';
    }

    /**
     * @return list<string>
     */
    public function getSortedListOfFiles(): array
    {
        if (!$this->useChapterFolders) {
            return $this->sortList($this->glob($this->rootPath . '*.' . $this->fileExtension));
        }

        $sortedFiles = [];
        $chapters = array_values(array_filter($this->glob($this->rootPath . '*'), 'is_dir'));

        foreach ($this->sortList($chapters) as $chapter) {
            $chapterFiles = $this->glob($chapter . '/*.' . $this->fileExtension);
            $sortedFiles = [...$sortedFiles, ...$this->sortList($chapterFiles)];
        }

        return $sortedFiles;
    }

    /**
     * @param list<string> $fileList
     */
    public function buildCommand(array $fileList, string $outputFile = 'book.pdf'): string
    {
        if ($fileList === []) {
            throw new \RuntimeException('No Markdown files found. Check the folder structure or the options.');
        }

        $outputPath = str_starts_with($outputFile, '/') ? $outputFile : $this->rootPath . $outputFile;

        return sprintf(
            'pandoc --pdf-engine=xelatex --toc -o %s %s',
            escapeshellarg($outputPath),
            implode(' ', array_map('escapeshellarg', $fileList)),
        );
    }

    /**
     * @param list<string> $fileList
     */
    public function generatePDF(array $fileList, string $outputFile = 'book.pdf'): void
    {
        $command = $this->buildCommand($fileList, $outputFile);

        exec($command . ' 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException(sprintf("pandoc failed with exit code %d:\n%s", $exitCode, implode("\n", $output)));
        }
    }

    /**
     * Orders paths by the leading number of their base name ("2-intro.md" before "10-outro.md"),
     * falling back to natural order for names without a number.
     *
     * @param list<string> $list
     *
     * @return list<string>
     */
    private function sortList(array $list): array
    {
        usort($list, static function (string $a, string $b): int {
            $indexA = self::leadingNumber($a);
            $indexB = self::leadingNumber($b);

            if ($indexA !== null && $indexB !== null && $indexA !== $indexB) {
                return $indexA <=> $indexB;
            }

            return strnatcasecmp(basename($a), basename($b));
        });

        return $list;
    }

    private static function leadingNumber(string $path): ?int
    {
        return preg_match('/^(\d+)/', basename($path), $matches) === 1 ? (int) $matches[1] : null;
    }

    /**
     * @return list<string>
     */
    private function glob(string $pattern): array
    {
        $result = glob($pattern);

        return $result === false ? [] : $result;
    }
}
