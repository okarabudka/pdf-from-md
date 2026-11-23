<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Okarabudka\PdfFromMd\FileProcessor;

$options = getopt('p:cf:o:', ['root-path:', 'using-chapter-folders', 'file-extension:', 'output:']);

$rootPath = $options['p'] ?? $options['root-path'] ?? null;
$fileExtension = $options['f'] ?? $options['file-extension'] ?? 'md';
$outputFile = $options['o'] ?? $options['output'] ?? 'book.pdf';
$useChapterFolders = isset($options['c']) || isset($options['using-chapter-folders']);

if (!is_string($rootPath) || !is_string($fileExtension) || !is_string($outputFile)) {
    fwrite(STDERR, "Usage: php bin/export.php -p <root-path> [-c] [-f <file-extension>] [-o <output.pdf>]\n");
    exit(1);
}

try {
    $processor = new FileProcessor($rootPath, $fileExtension, $useChapterFolders);
    $fileList = $processor->getSortedListOfFiles();

    foreach ($fileList as $file) {
        echo $file, "\n";
    }

    $processor->generatePDF($fileList, $outputFile);
    echo "PDF written to {$outputFile}\n";
} catch (\RuntimeException|\InvalidArgumentException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
