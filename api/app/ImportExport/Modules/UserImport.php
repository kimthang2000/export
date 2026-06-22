<?php

namespace App\ImportExport\Modules;

use App\ImportExport\Contracts\Importable;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserImport implements Importable
{
    public function label(): string
    {
        return 'users';
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function expectedColumns(): array
    {
        return ['name', 'email', 'password'];
    }

    public function validateRow(array $row, int $rowIndex): array
    {
        $errors = [];

        if (empty($row['name'] ?? '')) {
            $errors['name'] = "Row {$rowIndex}: Name is required.";
        }
        if (empty($row['email'] ?? '') || !filter_var($row['email'], \FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Row {$rowIndex}: Invalid email.";
        }
        if (User::where('email', $row['email'])->exists()) {
            $errors['email'] = "Row {$rowIndex}: Email already exists.";
        }
        if (empty($row['password'] ?? '')) {
            $errors['password'] = "Row {$rowIndex}: Password is required.";
        }

        return $errors;
    }

    public function processRow(array $row, array $options): void
    {
        User::create([
            'name' => $row['name'],
            'email' => $row['email'],
            'password' => Hash::make($row['password']),
        ]);
    }
}
