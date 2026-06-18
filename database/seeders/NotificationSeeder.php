<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('notifications')->insert([
            [
                'title' => 'Hệ thống HRM đã được khởi tạo',
                'content' => 'Chào mừng bạn đến với hệ thống quản lý nhân sự',
                'sender_id' => 1,
                'type' => 'system',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Bảng lương tháng 6',
                'content' => 'Lương tháng 6 đã được phê duyệt và sẵn sàng thanh toán',
                'sender_id' => 1,
                'type' => 'payroll',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Đơn nghỉ phép mới',
                'content' => 'Bạn có đơn nghỉ phép đang chờ duyệt',
                'sender_id' => 1,
                'type' => 'leave',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'KPI cập nhật',
                'content' => 'KPI tháng này đã được cập nhật',
                'sender_id' => 1,
                'type' => 'kpi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}