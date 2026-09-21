@php
    use App\Support\LeaveCapacityRules;

    $employeePercent = LeaveCapacityRules::toPercent(LeaveCapacityRules::RATIO_EMPLOYEE);
    $managerPercent = LeaveCapacityRules::toPercent(LeaveCapacityRules::RATIO_MANAGER_ACCOUNTANT);
@endphp

<div class="mb-4 rounded-2xl border border-sky-200 bg-sky-50/80 px-5 py-4 text-sm text-sky-900">
    <p class="font-bold text-sky-950">Quy tắc nghỉ phép (tham khảo khi duyệt)</p>
    <ul class="mt-2 space-y-1 text-xs list-disc list-inside">
        <li>Chủ nhật &amp; ngày Lễ không tính vào số ngày nghỉ; nghỉ nửa ngày = 0,5 ngày (một ngày, chọn sáng/chiều).</li>
        <li>Giới hạn cùng nghỉ: NV <strong>{{ $employeePercent }}%</strong>, QL/KT <strong>{{ $managerPercent }}%</strong> nhân sự đang làm việc / ngày (chỉ đơn đã duyệt; từ {{ \App\Support\LeaveCapacityRules::LONG_LEAVE_EXEMPT_FROM_DAYS }} ngày/đơn và loại thai sản/ốm/hiếu/kết hôn không tính).</li>
        <li>NV: QL phòng duyệt · QL: Admin duyệt · Không trùng đơn đã duyệt hoặc đang chờ.</li>
        <li>Lương: tối đa <strong>1 ngày công</strong> nghỉ hưởng lương/tháng; loại Nghỉ phép tối đa <strong>12 ngày/năm</strong> (pro-rata {{ \App\Support\LeaveAccrualRules::proRataDescription() }}); nghỉ không phép / vượt hạn mức → trừ <strong>300.000 ₫/ngày</strong>.</li>
    </ul>
</div>
