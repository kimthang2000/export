<?php

namespace App\ImportExport\Contracts;

interface ExportFormat
{
    public function write(string $filePath, \Generator $rows, array $columns): void;

    public function openFile(string $filePath, array $columns): void;

    public function writeRow(array $row): void;

    public function close(): void;

    public function extension(): string;

    public function contentType(): string;
}
