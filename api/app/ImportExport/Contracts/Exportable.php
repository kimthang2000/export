<?php

namespace App\ImportExport\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Exportable
{
    public function label(): string;

    public function query(): Builder;

    public function columns(): array;

    public function formatRow($model): array;

    public function chunkSize(): int;

    public function filters(): array;

    public function applyFilter(Builder $query, array $filter): Builder;
}
