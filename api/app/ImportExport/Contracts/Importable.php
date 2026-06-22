<?php

namespace App\ImportExport\Contracts;

interface Importable
{
    public function label(): string;

    public function chunkSize(): int;

    public function expectedColumns(): array;

    public function validateRow(array $row, int $rowIndex): array;

    public function processRow(array $row, array $options): void;
}
