<?php

namespace App\ImportExport\Formats\Import;

use App\ImportExport\Contracts\ImportFormat;

class CsvImporter implements ImportFormat
{
    public function read(string $filePath, int $offset = 0, ?int $limit = null): \Generator
    {
        $file = new \SplFileObject($filePath, 'r');
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY);

        $headers = $file->current();
        $file->next();

        $count = 0;
        $read = 0;

        while (!$file->eof()) {
            if ($count++ < $offset) {
                $file->next();
                continue;
            }
            if ($limit !== null && $read >= $limit) {
                break;
            }

            $row = $file->current();
            if ($row === [null]) {
                break;
            }

            $data = count($headers) === count($row) ? array_combine($headers, $row) : $row;
            yield $data;
            $read++;
            $file->next();
        }
    }

    public function countRows(string $filePath): int
    {
        $file = new \SplFileObject($filePath, 'r');
        $file->setFlags(\SplFileObject::READ_AHEAD);
        $file->seek(\PHP_INT_MAX);
        $total = $file->key() + 1;
        return max(0, $total - 1);
    }

    public function supportedExtensions(): array
    {
        return ['csv'];
    }
}
