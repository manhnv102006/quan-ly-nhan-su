<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departments')->insert([
            [
                'manager_id' => null,
                'department_code' => 'HR',
                'department_name' => 'Nhân sự',
                'description' => 'Quản lý tuyển dụng, nhân viên và hợp đồng lao động',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'manager_id' => null,
                'department_code' => 'IT',
                'department_name' => 'Công nghệ thông tin',
                'description' => 'Quản lý hệ thống, phần mềm và hạ tầng',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'manager_id' => null,
                'department_code' => 'ACC',
                'department_name' => 'Kế toán',
                'description' => 'Quản lý tài chính, chi phí và lương',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'manager_id' => null,
                'department_code' => 'MKT',
                'department_name' => 'Marketing',
                'description' => 'Truyền thông, quảng cáo và thương hiệu',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'manager_id' => null,
                'department_code' => 'SALE',
                'department_name' => 'Kinh doanh',
                'description' => 'Phát triển khách hàng và doanh thu',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}