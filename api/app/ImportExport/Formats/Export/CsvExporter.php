<?php

namespace App\ImportExport\Formats\Export;

use App\ImportExport\Contracts\ExportFormat;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer;

class CsvExporter implements ExportFormat
{
    private ?Writer $writer = null;

    public function write(string $filePath, \Generator $rows, array $columns): void
    {
        $this->openFile($filePath, $columns);
        foreach ($rows as $row) {
            $this->writeRow($row);
        }
        $this->close();
    }

    public function openFile(string $filePath, array $columns): void
    {
        $this->writer = new Writer();
        $this->writer->openToFile($filePath);
        $this->writer->addRow(Row::fromValues(array_values($columns)));
    }

    public function writeRow(array $row): void
    {
        $this->writer?->addRow(Row::fromValues(array_values($row)));
    }

    public function close(): void
    {
        $this->writer?->close();
        $this->writer = null;
    }

    public function extension(): string
    {
        return 'csv';
    }

    public function contentType(): string
    {
        return 'text/csv';
    }
}
