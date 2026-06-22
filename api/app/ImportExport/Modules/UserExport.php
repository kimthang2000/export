<?php

namespace App\ImportExport\Modules;

use App\ImportExport\Contracts\Exportable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserExport implements Exportable
{
    public function label(): string
    {
        return 'users';
    }

    public function query(): Builder
    {
        return User::query();
    }

    public function columns(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'created_at' => 'Created At',
        ];
    }

    public function formatRow($model): array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'email' => $model->email,
            'created_at' => $model->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function filters(): array
    {
        return [
            'created_at' => ['type' => 'date', 'operators' => ['=', '>', '<', '>=', '<=', 'between']],
            'name' => ['type' => 'string', 'operators' => ['=', 'like']],
            'email' => ['type' => 'string', 'operators' => ['=', 'like']],
        ];
    }

    public function applyFilter(Builder $query, array $filter): Builder
    {
        return match ($filter['operator']) {
            'between' => $query->whereBetween($filter['field'], $filter['value']),
            'in' => $query->whereIn($filter['field'], $filter['value']),
            'like' => $query->where($filter['field'], 'like', "%{$filter['value']}%"),
            default => $query->where($filter['field'], $filter['operator'], $filter['value']),
        };
    }
}
