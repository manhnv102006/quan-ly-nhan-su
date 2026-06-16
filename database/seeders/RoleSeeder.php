<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::ADMIN,
                'description' => 'Quản trị viên hệ thống - toàn quyền quản lý',
            ],
            [
                'name' => Role::MANAGER,
                'description' => 'Quản lý - quản lý nhân viên và phòng ban',
            ],
            [
                'name' => Role::EMPLOYEE,
                'description' => 'Nhân viên - truy cập thông tin cá nhân',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
