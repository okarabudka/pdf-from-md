# PDF from Markdown

A PHP command-line tool that compiles a folder of Markdown files into a single PDF book using `pandoc`.

## Context

Small CLI tool built in January 2025 to turn a set of numbered Markdown files or chapters into one PDF document with a table of contents.

## Features

- Collects files with a given extension (`md` by default) from a directory
- Sorts files by the numeric index in their names
- Optional chapter mode: each subdirectory is a chapter, chapters and their files are sorted by index
- Prints the sorted file list and generates `book.pdf` in the source directory via `pandoc --pdf-engine=xelatex --toc`

## Tech stack

- PHP 8.2 (CLI)
- pandoc, XeLaTeX (TeX Live)
- PHPUnit
- Composer, Docker Compose

## Architecture

- `bin/export.php`: CLI entry point; parses options with `getopt` and calls the processor
- `src/FileProcessor.php`: file discovery, sorting and PDF generation (namespace `Akrbdk\PdfFromMd`)
- `Dockerfile`: PHP 8.2 CLI image with pandoc and TeX Live, so no local installation is needed

## Getting started

```bash
docker compose up --build -d
docker compose run composer
docker exec -it pdf_from_md_php php bin/export.php -p /app/path/to/files -c -f md
```

The project directory is mounted at `/app` inside the container, so the source folder must be inside the project.

Options:

- `-p`, `--root-path` (required): directory with the Markdown files
- `-c`, `--using-chapter-folders` (optional): read files from chapter subdirectories
- `-f`, `--file-extension` (optional): file extension, `md` by default

Stop the containers with `docker compose down`.

## Testing

```bash
docker compose run composer test
```

This runs `phpunit --testdox` on `tests/FileProcessorTest.php`, which checks file ordering with and without chapter folders using the files in `tests/mock-files/`.

## Status

Finished small tool (2025), not actively maintained.

## License

MIT, see [LICENSE](LICENSE).
