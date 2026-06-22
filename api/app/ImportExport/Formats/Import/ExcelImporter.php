<?php

namespace App\ImportExport\Formats\Import;

use App\ImportExport\Contracts\ImportFormat;
use OpenSpout\Reader\XLSX\Reader;

class ExcelImporter implements ImportFormat
{
    public function read(string $filePath, int $offset = 0, ?int $limit = null): \Generator
    {
        $reader = new Reader();
        $reader->open($filePath);

        $sheet = $reader->getSheetIterator()->current();
        if (!$sheet) {
            $reader->close();
            return;
        }

        $headers = null;
        $skipped = 0;
        $read = 0;

        foreach ($sheet->getRowIterator() as $rowIndex => $row) {
            $cells = iterator_to_array($row->getCells());
            $values = array_map(fn($cell) => (string)$cell, $cells);

            if ($rowIndex === 1) {
                $headers = $values;
                continue;
            }

            if ($skipped < $offset) {
                $skipped++;
                continue;
            }
            if ($limit !== null && $read >= $limit) {
                break;
            }

            $data = count($headers) === count($values) ? array_combine($headers, $values) : $values;
            yield $data;
            $read++;
        }

        $reader->close();
    }

    public function countRows(string $filePath): int
    {
        $reader = new Reader();
        $reader->open($filePath);

        $sheet = $reader->getSheetIterator()->current();
        $count = 0;

        if ($sheet) {
            foreach ($sheet->getRowIterator() as $index => $row) {
                if ($index > 1) {
                    $count++;
                }
            }
        }

        $reader->close();
        return $count;
    }

    public function supportedExtensions(): array
    {
        return ['xlsx'];
    }
}
