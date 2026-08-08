@php
    $grace = \App\Services\EmployeeAttendanceService::EARLY_LEAVE_GRACE_MINUTES;
@endphp

<div class="mb-4 rounded-2xl border border-violet-200 bg-violet-50/80 px-5 py-4 text-sm text-violet-900">
    <p class="font-bold text-violet-950">Quy tắc về sớm (tham khảo khi duyệt đơn)</p>
    <ul class="mt-2 space-y-1 text-xs list-disc list-inside text-violet-900">
        <li>Không có đơn duyệt: miễn {{ $grace }} phút trước giờ tan ca; vượt quá → trừ lương theo phút (sau {{ $grace }}p miễn trừ).</li>
        <li>Đơn <strong>đã duyệt</strong> trong ngày → nhân viên check-out sớm <strong>không bị trừ lương</strong> vì về sớm.</li>
        <li>Ca hành chính: mỗi buổi (12:00 / 17:00) áp dụng riêng {{ $grace }} phút miễn trừ.</li>
    </ul>
</div>
