<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạm thời chỉ khởi tạo các Role cơ bản, chưa làm Permission
        $roles = [
            'admin',
            'member'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Hiển thị thông báo trên console khi chạy seed
        $this->command->info('Init roles successfully!');
    }
}
