@php
    $grace = \App\Services\EmployeeAttendanceService::EARLY_LEAVE_GRACE_MINUTES;
    $workHours = \App\Services\PayrollService::WORK_HOURS_PER_DAY;
@endphp

<div class="bg-violet-50 border border-violet-200 rounded-3xl p-5 sm:p-6">
    <h2 class="text-sm font-bold text-violet-900 mb-2">Quy định về sớm</h2>
    <ul class="text-xs text-violet-900 space-y-2 list-disc list-inside">
        <li>
            <strong>Miễn trừ {{ $grace }} phút</strong> trước giờ tan ca — check-out trong khoảng này
            <strong>không bị trừ lương</strong>.
            <span class="text-violet-700">(Ví dụ tan ca 17:00 → checkout từ 16:40 đến 17:00 đều không phạt.)</span>
        </li>
        <li>
            Về sớm <strong>hơn {{ $grace }} phút</strong> so với giờ tan ca → hệ thống ghi nhận phút về sớm
            và <strong>trừ lương theo số phút</strong> (chỉ tính phần <em>sau</em> {{ $grace }} phút miễn trừ).
            <span class="text-violet-700">(Ví dụ tan ca 17:00, checkout 16:00 → về sớm 60 phút, trừ lương <strong>40 phút</strong>.)</span>
        </li>
        <li>
            <strong>Lương/giờ</strong> = Lương hợp đồng ÷ (Ngày công chuẩn trong kỳ × {{ $workHours }}).
            Số tiền trừ = (phút về sớm bị tính ÷ 60) × lương/giờ.
        </li>
        <li>
            <strong>Đơn xin về sớm được duyệt</strong> trong ngày → checkout sớm
            <strong>không bị ghi phút về sớm, không trừ lương</strong>.
            Nên gửi đơn trước khi về sớm có lý do chính đáng.
        </li>
        <li>
            <strong>Ca hành chính (sáng + chiều):</strong> mỗi buổi áp dụng riêng — tan buổi sáng 12:00,
            tan buổi chiều 17:00; mỗi buổi đều có {{ $grace }} phút miễn trừ trước giờ tan ca.
        </li>
        <li>
            Phạt về sớm được cộng vào <strong>khấu trừ</strong> khi tính bảng lương cuối kỳ
            (xem chi tiết tại bảng lương / lịch sử chấm công).
        </li>
    </ul>
</div>
