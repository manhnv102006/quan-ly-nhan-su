<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('departments')->insert([
            [
                'manager_id' => null,
                'department_code' => 'HR',
                'department_name' => 'Phòng Nhân sự',
                'description' => 'Quản lý nhân sự và tuyển dụng',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'manager_id' => null,
                'department_code' => 'IT',
                'department_name' => 'Phòng Công nghệ Thông tin',
                'description' => 'Quản lý hệ thống và phát triển phần mềm',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'manager_id' => null,
                'department_code' => 'ACC',
                'department_name' => 'Phòng Kế toán',
                'description' => 'Quản lý tài chính kế toán',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'manager_id' => null,
                'department_code' => 'SALE',
                'department_name' => 'Phòng Kinh doanh',
                'description' => 'Phụ trách kinh doanh và chăm sóc khách hàng',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'manager_id' => null,
                'department_code' => 'MKT',
                'department_name' => 'Phòng Marketing',
                'description' => 'Quảng bá thương hiệu và sản phẩm',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}