<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use App\Support\LeaveTypeDefaults;
use App\Support\LeaveTypeRegistry;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Đồng bộ lại danh mục loại nghỉ phép mặc định.
     *
     * Chỉ tạo mới loại còn thiếu — cấu hình Admin đã chỉnh trên các loại sẵn có
     * được giữ nguyên.
     */
    public function run(): void
    {
        foreach (LeaveTypeDefaults::rows() as $row) {
            LeaveType::query()->firstOrCreate(['code' => $row['code']], $row);
        }

        LeaveTypeRegistry::flush();
    }
}
