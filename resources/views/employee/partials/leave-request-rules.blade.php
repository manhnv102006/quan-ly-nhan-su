@php
    use App\Models\LeaveRequest;
    use App\Support\LeaveCapacityRules;

    $leaveCapacityPercent = $leaveCapacityPercent ?? LeaveCapacityRules::toPercent(LeaveCapacityRules::RATIO_EMPLOYEE);
    $employeePercent = LeaveCapacityRules::toPercent(LeaveCapacityRules::RATIO_EMPLOYEE);
    $managerPercent = LeaveCapacityRules::toPercent(LeaveCapacityRules::RATIO_MANAGER_ACCOUNTANT);
    $paidTypes = LeaveRequest::paidLeaveTypes();
    $typeLabels = LeaveRequest::LEAVE_TYPE_LABELS;
@endphp

<div class="bg-sky-50 border border-sky-200 rounded-3xl p-5 sm:p-6 lg:p-8">
    <h2 class="text-sm font-bold text-sky-900 mb-4 sm:mb-5">Quy định nghỉ phép</h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
        {{-- Cột trái --}}
        <div class="space-y-5">
            <div>
                <p class="font-bold text-sky-950 mb-2 text-xs uppercase tracking-wide">Loại nghỉ phép</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($typeLabels as $key => $label)
                        <div class="rounded-xl border border-sky-100 bg-white/80 px-3 py-2.5 shadow-sm">
                            <p class="text-xs font-semibold text-slate-800">{{ $label }}</p>
                            <p class="mt-0.5 text-[11px] leading-snug">
                                @if (in_array($key, $paidTypes, true))
                                    <span class="text-emerald-700 font-medium">Hưởng lương</span>
                                    @if ($key === 'half_day')
                                        <span class="text-sky-700"> · 0,5 ngày</span>
                                    @endif
                                @elseif ($key === 'unpaid')
                                    <span class="text-rose-700 font-medium">Không lương</span>
                                @else
                                    <span class="text-slate-500">Theo quyết định duyệt</span>
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="font-bold text-sky-950 mb-2 text-xs uppercase tracking-wide">Cách tính số ngày nghỉ</p>
                <ul class="space-y-1.5 text-xs text-sky-900 list-disc list-inside leading-relaxed">
                    <li><strong>Chủ nhật</strong> và <strong>ngày Lễ</strong> không tính vào số ngày nghỉ.</li>
                    <li><strong>Nghỉ nửa ngày:</strong> một ngày duy nhất, tính <strong>0,5 ngày</strong>; không áp dụng CN / Lễ.</li>
                    <li>Không gửi đơn nếu cả khoảng thời gian chỉ rơi vào CN hoặc ngày Lễ.</li>
                </ul>
            </div>
        </div>

        {{-- Cột phải --}}
        <div class="space-y-5">
            <div>
                <p class="font-bold text-sky-950 mb-2 text-xs uppercase tracking-wide">Giới hạn phòng ban</p>
                <ul class="space-y-1.5 text-xs text-sky-900 list-disc list-inside leading-relaxed">
                    <li>Nhân viên: tối đa <strong>{{ $employeePercent }}%</strong> nhân sự đang làm việc / ngày.</li>
                    <li>Quản lý / Kế toán: tối đa <strong>{{ $managerPercent }}%</strong> / ngày.</li>
                    <li>
                        Hạn mức của bạn: <strong>{{ $leaveCapacityPercent }}%</strong>
                        @if ($leaveCapacityPercent === $managerPercent)
                            (QL / KT)
                        @else
                            (NV)
                        @endif
                        — VD phòng 10 người → tối đa {{ max(1, (int) floor(10 * $leaveCapacityPercent / 100)) }} người/ngày.
                    </li>
                    <li>Chỉ tính đơn <strong>đã duyệt</strong>; đơn chờ duyệt chưa chiếm chỗ.</li>
                    <li>Đơn nghỉ <strong>trên {{ \App\Support\LeaveCapacityRules::CAPACITY_EXEMPT_ABOVE_DAYS }} ngày công</strong> không tính vào giới hạn phòng ban.</li>
                </ul>
            </div>

            <div>
                <p class="font-bold text-sky-950 mb-2 text-xs uppercase tracking-wide">Quy trình duyệt</p>
                <ul class="space-y-1.5 text-xs text-sky-900 list-disc list-inside leading-relaxed">
                    <li>Nhân viên → <strong>Quản lý phòng ban</strong> duyệt / từ chối.</li>
                    <li>Quản lý → <strong>Admin</strong> duyệt / từ chối.</li>
                    <li>Không trùng khoảng nghỉ với đơn <strong>đã duyệt</strong>.</li>
                    <li>Gửi đơn trước ngày nghỉ, kèm lý do (bắt buộc).</li>
                </ul>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50/80 px-4 py-3">
                <p class="font-bold text-amber-950 mb-1.5 text-xs uppercase tracking-wide">Ảnh hưởng lương</p>
                <ul class="space-y-1.5 text-xs text-amber-950 list-disc list-inside leading-relaxed">
                    <li>Tối đa <strong>1 ngày công</strong> nghỉ hưởng lương / tháng (nửa ngày = 0,5).</li>
                    <li>Nghỉ không phép / vượt hạn mức / không lương → trừ <strong>300.000 ₫ / ngày</strong>.</li>
                    <li>Đơn duyệt đúng loại hưởng lương → tính ngày công, không phạt 300k/ngày.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
