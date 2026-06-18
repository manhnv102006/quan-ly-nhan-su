<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('employees')->insert([
            [
                'user_id' => null,
                'department_id' => 1,
                'position_id' => 1,
                'employee_code' => 'EMP001',
                'full_name' => 'Nguyễn Văn An',
                'gender' => 'male',
                'date_of_birth' => '1990-05-15',
                'phone' => '0912345678',
                'email' => 'an@example.com',
                'address' => 'Hà Nội',
                'avatar' => null,
                'hire_date' => '2025-01-01',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => null,
                'department_id' => 2,
                'position_id' => 2,
                'employee_code' => 'EMP002',
                'full_name' => 'Trần Thị Bình',
                'gender' => 'female',
                'date_of_birth' => '1995-08-20',
                'phone' => '0912345679',
                'email' => 'binh@example.com',
                'address' => 'Hồ Chí Minh',
                'avatar' => null,
                'hire_date' => '2025-02-01',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => null,
                'department_id' => 3,
                'position_id' => 3,
                'employee_code' => 'EMP003',
                'full_name' => 'Lê Văn Cường',
                'gender' => 'male',
                'date_of_birth' => '1992-03-10',
                'phone' => '0912345680',
                'email' => 'cuong@example.com',
                'address' => 'Đà Nẵng',
                'avatar' => null,
                'hire_date' => '2025-03-01',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => null,
                'department_id' => 4,
                'position_id' => 4,
                'employee_code' => 'EMP004',
                'full_name' => 'Phạm Thị Dung',
                'gender' => 'female',
                'date_of_birth' => '1998-11-25',
                'phone' => '0912345681',
                'email' => 'dung@example.com',
                'address' => 'Hải Phòng',
                'avatar' => null,
                'hire_date' => '2025-04-01',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => null,
                'department_id' => 5,
                'position_id' => 5,
                'employee_code' => 'EMP005',
                'full_name' => 'Hoàng Văn Em',
                'gender' => 'male',
                'date_of_birth' => '2000-07-12',
                'phone' => '0912345682',
                'email' => 'em@example.com',
                'address' => 'Cần Thơ',
                'avatar' => null,
                'hire_date' => '2025-05-01',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}