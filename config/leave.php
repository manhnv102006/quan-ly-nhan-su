<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Phép năm
    |--------------------------------------------------------------------------
    */
    'annual_leave_days' => (float) env('LEAVE_ANNUAL_DAYS', 12),

    'monthly_paid_days' => (float) env('LEAVE_MONTHLY_PAID_DAYS', 1),

    /*
    |--------------------------------------------------------------------------
    | Pro-rata: quy ước tháng vào làm
    |--------------------------------------------------------------------------
    |
    | mid_month_cutoff_day: ngày cắt trong tháng (1–28).
    |   - Vào từ ngày 1 đến ngày cắt: luôn tính tháng vào làm.
    |
    | mid_month_after_cutoff:
    |   - current_month: vào sau ngày cắt vẫn tính tròn tháng vào làm.
    |   - next_month: vào sau ngày cắt bắt đầu cộng phép từ tháng sau.
    |
    */
    'mid_month_cutoff_day' => (int) env('LEAVE_MID_MONTH_CUTOFF_DAY', 15),

    'mid_month_after_cutoff' => env('LEAVE_MID_MONTH_AFTER_CUTOFF', 'next_month'),

    /*
    |--------------------------------------------------------------------------
    | Scheduler cộng phép cuối tháng
    |--------------------------------------------------------------------------
    */
    'accrual_days_per_month' => (float) env('LEAVE_ACCRUAL_DAYS_PER_MONTH', 1),

    'accrual_schedule_time' => env('LEAVE_ACCRUAL_SCHEDULE_TIME', '23:30'),

    /*
    |--------------------------------------------------------------------------
    | Nghỉ không lương dài hạn — chặn cộng phép
    |--------------------------------------------------------------------------
    |
    | Nếu tổng ngày nghỉ không lương (ngày làm việc, đã duyệt) lũy kế trong năm
    | vượt ngưỡng thì tháng cộng phép đó bị bỏ qua. Đặt 0 để tắt.
    |
    */
    'unpaid_leave_accrual_block_days' => (float) env('LEAVE_UNPAID_ACCRUAL_BLOCK_DAYS', 12),

    /*
    |--------------------------------------------------------------------------
    | Nghỉ thai sản / nghỉ ốm BHXH — cộng phép
    |--------------------------------------------------------------------------
    |
    | Nghỉ thai sản: vẫn cộng phép trong tháng nghỉ (không bị chặn bởi quy tắc ốm/KL).
    |
    | sick_leave_accrual_allowed_months: số tháng nghỉ ốm (đã duyệt) trong năm vẫn
    | được cộng phép; từ tháng ốm thứ (N+1) trở đi không cộng. Đặt 0 để tắt.
    |
    */
    'sick_leave_accrual_allowed_months' => (int) env('LEAVE_SICK_ACCRUAL_ALLOWED_MONTHS', 2),

    /*
    |--------------------------------------------------------------------------
    | Chuyển phép năm (carried_over)
    |--------------------------------------------------------------------------
    |
    | Phép năm trước còn dư được chuyển sang năm sau; hết hạn cuối tháng
    | carry_over_expiry_month (mặc định 4 = hết tháng 4). carry_over_max_days: null = không giới hạn.
    |
    */
    'carry_over_enabled' => (bool) env('LEAVE_CARRY_OVER_ENABLED', true),

    'carry_over_expiry_month' => (int) env('LEAVE_CARRY_OVER_EXPIRY_MONTH', 4),

    'carry_over_max_days' => env('LEAVE_CARRY_OVER_MAX_DAYS'),

    /*
    |--------------------------------------------------------------------------
    | Giới hạn nghỉ phòng ban
    |--------------------------------------------------------------------------
    |
    | block: vượt hạn mức thì chặn duyệt.
    | override: quản lý/admin có thể duyệt vượt kèm lý do (ghi vào lịch sử đơn).
    |
    */
    'department_capacity_enforcement' => env('LEAVE_DEPARTMENT_CAPACITY_ENFORCEMENT', 'override'),

];
