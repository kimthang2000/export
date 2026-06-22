<?php

namespace App\ImportExport\Contracts;

interface ImportFormat
{
    public function read(string $filePath, int $offset = 0, ?int $limit = null): \Generator;

    public function countRows(string $filePath): int;

    public function supportedExtensions(): array;
}
