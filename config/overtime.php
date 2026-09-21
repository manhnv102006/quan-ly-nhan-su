<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Giờ làm việc bình thường mặc định
    |--------------------------------------------------------------------------
    |
    | Dùng khi nhân viên chưa được phân ca trong ngày tăng ca.
    |
    */
    'default_normal_hours_per_day' => (float) env('OVERTIME_DEFAULT_NORMAL_HOURS', 8),

    /*
    |--------------------------------------------------------------------------
    | Giới hạn tăng ca theo ngày
    |--------------------------------------------------------------------------
    |
    | daily_overtime_ratio: tối đa 50% số giờ làm bình thường trong ngày.
    | max_total_hours_per_day: tổng giờ làm bình thường + tăng ca không quá 12h/ngày.
    |
    */
    'daily_overtime_ratio' => (float) env('OVERTIME_DAILY_RATIO', 0.5),

    'max_total_hours_per_day' => (float) env('OVERTIME_MAX_TOTAL_HOURS_PER_DAY', 12),

    /*
    |--------------------------------------------------------------------------
    | Giới hạn tăng ca theo tháng / năm
    |--------------------------------------------------------------------------
    */
    'max_hours_per_month' => (float) env('OVERTIME_MAX_HOURS_PER_MONTH', 40),

    'max_hours_per_year' => (float) env('OVERTIME_MAX_HOURS_PER_YEAR', 200),

    /*
    |--------------------------------------------------------------------------
    | Ngành đặc thù — tối đa 300 giờ/năm
    |--------------------------------------------------------------------------
    |
    | Bật extended_annual_limit khi công ty thuộc các ngành: dệt may, da giày,
    | điện, điện tử, chế biến nông lâm thủy sản, sản xuất/gia công xuất khẩu,
    | cung cấp điện, viễn thông, nước...
    |
    */
    'extended_annual_limit' => (bool) env('OVERTIME_EXTENDED_ANNUAL_LIMIT', false),

    'max_hours_per_year_extended' => (float) env('OVERTIME_MAX_HOURS_PER_YEAR_EXTENDED', 300),

    /*
    |--------------------------------------------------------------------------
    | Cảnh báo tiến gần ngưỡng
    |--------------------------------------------------------------------------
    |
    | warning_ratio: cảnh báo khi đạt 80% giới hạn tháng/năm (32h/tháng, 160h/năm).
    | labor_department_notification_hours: từ mốc này nhắc thông báo Sở LĐTBXH.
    |
    */
    'warning_ratio' => (float) env('OVERTIME_WARNING_RATIO', 0.8),

    'labor_department_notification_hours' => (float) env('OVERTIME_LABOR_DEPT_NOTIFICATION_HOURS', 200),

    /*
    |--------------------------------------------------------------------------
    | Khung giờ nghỉ nửa ngày — dùng chặn OT trùng buổi nghỉ
    |--------------------------------------------------------------------------
    */
    'half_day_leave_windows' => [
        'morning' => ['08:00', '12:00'],
        'afternoon' => ['13:00', '17:00'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Lý do / công việc tăng ca
    |--------------------------------------------------------------------------
    |
    | Bắt buộc mô tả rõ để phục vụ giải trình với Sở LĐTBXH khi thanh tra.
    |
    */
    'min_reason_length' => (int) env('OVERTIME_MIN_REASON_LENGTH', 10),

];
