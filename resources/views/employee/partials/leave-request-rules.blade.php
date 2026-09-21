@php
    use App\Models\LeaveRequest;
    use App\Support\LeaveCapacityRules;

    $leaveCapacityPercent = $leaveCapacityPercent ?? LeaveCapacityRules::toPercent(LeaveCapacityRules::RATIO_EMPLOYEE);
    $employeePercent = LeaveCapacityRules::toPercent(LeaveCapacityRules::RATIO_EMPLOYEE);
    $managerPercent = LeaveCapacityRules::toPercent(LeaveCapacityRules::RATIO_MANAGER_ACCOUNTANT);
    $typeLabels = $typeLabels ?? LeaveRequest::leaveTypeLabels();
    $typeConfigs = \App\Support\LeaveTypeRegistry::all();
    $documentTypeNames = collect($typeLabels)
        ->filter(fn ($label, $key) => $typeConfigs->get($key)?->requiresDocument())
        ->values()
        ->all();
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
                        @php $config = $typeConfigs->get($key); @endphp
                        <div class="rounded-xl border border-sky-100 bg-white/80 px-3 py-2.5 shadow-sm">
                            <p class="text-xs font-semibold text-slate-800">{{ $label }}</p>
                            <p class="mt-0.5 text-[11px] leading-snug">
                                @if ($config?->salary_payer === \App\Models\LeaveType::PAYER_COMPANY)
                                    <span class="text-emerald-700 font-medium">Công ty trả lương</span>
                                @elseif ($config?->salary_payer === \App\Models\LeaveType::PAYER_INSURANCE)
                                    <span class="text-sky-700 font-medium">{{ $config->salaryPayerLabel() }}</span>
                                @elseif ($config?->salary_payer === \App\Models\LeaveType::PAYER_NONE)
                                    <span class="text-rose-700 font-medium">Không lương</span>
                                @else
                                    <span class="text-slate-500">Theo quyết định duyệt</span>
                                @endif
                                @if ($config?->deductsAnnualLeave())
                                    <span class="text-slate-500"> · Trừ phép năm</span>
                                @endif
                            </p>
                            @if ($config?->quotaLabel())
                                <p class="mt-1 text-[11px] leading-snug text-slate-500">Hạn mức: {{ $config->quotaLabel() }}</p>
                            @endif
                            @if ($config?->requiresDocument())
                                <p class="mt-1 text-[11px] leading-snug text-amber-700">Cần giấy tờ minh chứng</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="font-bold text-sky-950 mb-2 text-xs uppercase tracking-wide">Cách tính số ngày nghỉ</p>
                <ul class="space-y-1.5 text-xs text-sky-900 list-disc list-inside leading-relaxed">
                    <li><strong>Chủ nhật</strong> và <strong>ngày Lễ</strong> không tính vào số ngày nghỉ.</li>
                    <li><strong>Nghỉ nửa ngày:</strong> một ngày duy nhất, chọn <strong>sáng</strong> hoặc <strong>chiều</strong>, trừ <strong>0,5 ngày</strong>; không áp dụng CN / Lễ.</li>
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
                    <li>Đơn nghỉ <strong>từ {{ \App\Support\LeaveCapacityRules::LONG_LEAVE_EXEMPT_FROM_DAYS }} ngày công trở lên</strong> không tính vào giới hạn phòng ban.</li>
                    <li>Loại nghỉ theo luật (<strong>thai sản, ốm, hiếu, kết hôn</strong>) không bị giới hạn phòng ban.</li>
                    <li>Mẫu số nhân sự đang làm việc <strong>không gồm</strong> người nghỉ việc, đang thử việc hoặc nghỉ thai sản dài hạn.</li>
                </ul>
            </div>

            <div>
                <p class="font-bold text-sky-950 mb-2 text-xs uppercase tracking-wide">Quy trình duyệt</p>
                <ul class="space-y-1.5 text-xs text-sky-900 list-disc list-inside leading-relaxed">
                    <li>Nhân viên → <strong>Quản lý phòng ban</strong> duyệt / từ chối.</li>
                    <li>Quản lý → <strong>Admin</strong> duyệt / từ chối.</li>
                    <li>Không trùng khoảng nghỉ với đơn <strong>đã duyệt</strong> hoặc <strong>đang chờ duyệt</strong>.</li>
                    <li>Gửi đơn trước ngày nghỉ, kèm lý do (bắt buộc).</li>
                    @if ($documentTypeNames !== [])
                        <li><strong>{{ implode(', ', $documentTypeNames) }}:</strong> bắt buộc đính kèm giấy tờ minh chứng (PDF/JPG/PNG).</li>
                    @endif
                </ul>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50/80 px-4 py-3">
                <p class="font-bold text-amber-950 mb-1.5 text-xs uppercase tracking-wide">Ảnh hưởng lương</p>
                <ul class="space-y-1.5 text-xs text-amber-950 list-disc list-inside leading-relaxed">
                    <li>Tối đa <strong>1 ngày công</strong> nghỉ hưởng lương / tháng (nửa ngày = 0,5).</li>
                    <li>Loại <strong>Nghỉ phép</strong>: tối đa <strong>12 ngày</strong> / năm (đã duyệt); cuối tháng hệ thống tự cộng <strong>1 ngày/tháng</strong>. {{ \App\Support\LeaveAccrualRules::proRataDescription() }}</li>
                    <li><strong>Nghỉ không lương dài hạn:</strong> lũy kế vượt <strong>{{ (int) config('leave.unpaid_leave_accrual_block_days', 12) }} ngày làm việc/năm</strong> (đã duyệt) → tháng đó <strong>không cộng phép</strong>.</li>
                    <li><strong>Nghỉ thai sản:</strong> vẫn <strong>cộng phép</strong> trong tháng nghỉ thai sản (đã duyệt).</li>
                    <li><strong>Nghỉ ốm BHXH:</strong> tối đa <strong>{{ (int) config('leave.sick_leave_accrual_allowed_months', 2) }} tháng/năm</strong> vẫn cộng phép; tháng ốm vượt hạn <strong>không cộng</strong>.</li>
                    <li><strong>Phép chuyển năm:</strong> phép năm trước còn dư chuyển sang (<em>carried_over</em>), ưu tiên dùng trước, <strong>hết hạn cuối tháng {{ (int) config('leave.carry_over_expiry_month', 4) }}</strong> năm mới.</li>
                    <li>Nghỉ không phép / vượt hạn mức / không lương → trừ <strong>300.000 ₫ / ngày</strong>.</li>
                    <li>Đơn duyệt đúng loại hưởng lương → tính ngày công, không phạt 300k/ngày.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
