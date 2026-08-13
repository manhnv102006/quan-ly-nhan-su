@php
    $user = Auth::user();
    $isManager = $user->role->name === 'manager';
    $layout = $isManager ? 'manager-layout' : 'employee-layout';
    $layoutParams = [
        'title'    => 'Tạo đơn xin về sớm',
        'subtitle' => 'Điền thông tin để gửi yêu cầu về sớm.',
    ];
    $grace = \App\Services\EmployeeAttendanceService::EARLY_LEAVE_GRACE_MINUTES;
    $recentRequests = $recentRequests ?? collect();
    $stats = $stats ?? ['pending' => 0, 'approved' => 0, 'rejected' => 0];
    $shiftSchedule = $shiftSchedule ?? collect();
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">

    <div class="space-y-6">
        <a href="{{ route('employee.early-leave.index') }}"
           class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 transition font-semibold">
            <span>←</span> Quay lại danh sách
        </a>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 xl:gap-8 items-start">
            {{-- Form --}}
            <div>
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8 h-full">
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-slate-800">Đơn xin về sớm</h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Điền ngày, giờ muốn về và lý do. Sau khi quản lý duyệt, bạn check-out sớm trong ngày đó sẽ không bị trừ lương.
                        </p>
                    </div>

                    <form id="early-leave-form" action="{{ route('employee.early-leave.store') }}" method="POST" class="space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label for="request_date" class="block text-xs font-bold text-slate-500 uppercase mb-2">
                                    Ngày xin về sớm <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" id="request_date" name="request_date"
                                       min="{{ today()->toDateString() }}"
                                       value="{{ old('request_date', today()->toDateString()) }}"
                                       required
                                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm">
                                <div id="request-date-hint" class="hidden mt-2 text-xs font-medium"></div>
                                @error('request_date')
                                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="leave_time" class="block text-xs font-bold text-slate-500 uppercase mb-2">
                                    Giờ muốn về <span class="text-rose-500">*</span>
                                </label>
                                <input type="time" id="leave_time" name="leave_time"
                                       value="{{ old('leave_time') }}"
                                       required
                                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm">
                                @error('leave_time')
                                    <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div id="leave-time-hint" class="hidden rounded-2xl border px-4 py-3 text-xs leading-relaxed"></div>

                        <div>
                            <label for="reason" class="block text-xs font-bold text-slate-500 uppercase mb-2">
                                Lý do <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="reason" name="reason" rows="6" required
                                      placeholder="Ví dụ: Đi khám bệnh, có việc gia đình khẩn cấp..."
                                      class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm resize-none">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                            <p class="text-[11px] text-slate-400 mt-1.5">Mô tả rõ lý do để quản lý duyệt nhanh hơn (tối đa 500 ký tự).</p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3 text-xs text-slate-600 leading-relaxed">
                            <p class="font-semibold text-slate-700 mb-1">Trước khi gửi, hãy kiểm tra:</p>
                            <ul class="list-disc list-inside space-y-0.5">
                                <li>Ngày và giờ về sớm đúng với kế hoạch thực tế.</li>
                                <li>Lý do cụ thể, trung thực — tránh gửi đơn trùng ngày đã có.</li>
                                <li>Nếu về trong {{ $grace }} phút trước tan ca, có thể không cần đơn (xem bảng minh họa bên phải).</li>
                            </ul>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3 pt-1">
                            <a href="{{ route('employee.early-leave.index') }}"
                               class="flex-1 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold text-center hover:bg-slate-50 transition">
                                Hủy
                            </a>
                            <button type="submit" id="early-leave-submit"
                                    class="flex-1 py-3 rounded-xl bg-violet-600 text-white text-sm font-bold shadow-md shadow-violet-500/20 hover:bg-violet-700 transition disabled:opacity-50 disabled:pointer-events-none">
                                Gửi đơn
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sidebar --}}
            <aside class="space-y-5">
                {{-- Thống kê nhanh --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-amber-100 bg-amber-50 px-3 py-3 text-center">
                        <p class="text-lg font-bold text-amber-700">{{ $stats['pending'] }}</p>
                        <p class="text-[10px] font-semibold uppercase text-amber-600 mt-0.5">Chờ duyệt</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-3 py-3 text-center">
                        <p class="text-lg font-bold text-emerald-700">{{ $stats['approved'] }}</p>
                        <p class="text-[10px] font-semibold uppercase text-emerald-600 mt-0.5">Đã duyệt</p>
                    </div>
                    <div class="rounded-2xl border border-rose-100 bg-rose-50 px-3 py-3 text-center">
                        <p class="text-lg font-bold text-rose-700">{{ $stats['rejected'] }}</p>
                        <p class="text-[10px] font-semibold uppercase text-rose-600 mt-0.5">Từ chối</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-slate-800">Quy trình duyệt</h3>
                            <ol class="mt-3 space-y-2.5 text-xs text-slate-600">
                                <li class="flex gap-2">
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-violet-100 text-[10px] font-bold text-violet-700">1</span>
                                    <span>Bạn gửi đơn kèm ngày, giờ và lý do về sớm.</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-violet-100 text-[10px] font-bold text-violet-700">2</span>
                                    <span>Quản lý trực tiếp xem xét và phê duyệt hoặc từ chối.</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-violet-100 text-[10px] font-bold text-violet-700">3</span>
                                    <span>Đơn <strong class="text-slate-800">được duyệt</strong> → check-out sớm trong ngày không bị trừ lương.</span>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>

                {{-- Giờ tan ca & minh họa --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800">Giờ tan ca &amp; minh họa phạt</h3>
                    <p class="text-xs text-slate-500 mt-1">Ca hành chính — mỗi buổi có {{ $grace }} phút miễn trừ trước giờ tan ca.</p>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-sky-50 border border-sky-100 px-3 py-3 text-center">
                            <p class="text-[10px] font-bold uppercase text-sky-600">Buổi sáng</p>
                            <p class="text-sm font-bold text-sky-900 mt-1">Tan ca 12:00</p>
                            <p class="text-[10px] text-sky-700 mt-0.5">Miễn từ 11:40</p>
                        </div>
                        <div class="rounded-2xl bg-indigo-50 border border-indigo-100 px-3 py-3 text-center">
                            <p class="text-[10px] font-bold uppercase text-indigo-600">Buổi chiều</p>
                            <p class="text-sm font-bold text-indigo-900 mt-1">Tan ca 17:00</p>
                            <p class="text-[10px] text-indigo-700 mt-0.5">Miễn từ 16:40</p>
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-100">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500">
                                    <th class="px-3 py-2 text-left font-bold">Tình huống</th>
                                    <th class="px-3 py-2 text-left font-bold">Không có đơn</th>
                                    <th class="px-3 py-2 text-left font-bold">Có đơn duyệt</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <tr>
                                    <td class="px-3 py-2.5">Checkout 16:50 (tan ca 17:00)</td>
                                    <td class="px-3 py-2.5 text-emerald-700 font-medium">Không phạt</td>
                                    <td class="px-3 py-2.5 text-emerald-700 font-medium">Không phạt</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2.5">Checkout 16:00 (tan ca 17:00)</td>
                                    <td class="px-3 py-2.5 text-rose-700 font-medium">Trừ 40 phút lương</td>
                                    <td class="px-3 py-2.5 text-emerald-700 font-medium">Không phạt</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2.5">Checkout 11:50 (tan ca 12:00)</td>
                                    <td class="px-3 py-2.5 text-emerald-700 font-medium">Không phạt</td>
                                    <td class="px-3 py-2.5 text-emerald-700 font-medium">Không phạt</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                @include('employee.partials.early-leave-rules')

                <div class="rounded-3xl border border-emerald-200 bg-emerald-50/80 p-5 sm:p-6">
                    <h3 class="text-sm font-bold text-emerald-900">Lưu ý nhanh</h3>
                    <ul class="mt-3 space-y-2 text-xs text-emerald-900 list-disc list-inside">
                        <li>Nên gửi đơn <strong>trước</strong> khi về sớm để tránh bị ghi nhận phút về sớm.</li>
                        <li>Miễn trừ <strong>{{ $grace }} phút</strong> trước giờ tan ca nếu không có đơn duyệt.</li>
                        <li>Ca hành chính: mỗi buổi (12:00 / 17:00) tính riêng {{ $grace }} phút miễn trừ.</li>
                        <li>Theo dõi trạng thái đơn tại mục <strong>Về sớm</strong> trên menu.</li>
                    </ul>
                </div>

                {{-- Đơn gần đây --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h3 class="text-sm font-bold text-slate-800">Đơn gần đây của bạn</h3>
                        <a href="{{ route('employee.early-leave.index') }}" class="text-[11px] font-semibold text-violet-600 hover:underline shrink-0">
                            Xem tất cả →
                        </a>
                    </div>

                    @if ($recentRequests->isEmpty())
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center">
                            <p class="text-xs text-slate-500">Bạn chưa gửi đơn về sớm nào.</p>
                            <p class="text-[11px] text-slate-400 mt-1">Điền form bên trái để tạo đơn đầu tiên.</p>
                        </div>
                    @else
                        <ul class="space-y-3">
                            @foreach ($recentRequests as $req)
                                <li class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50/60 px-3 py-3">
                                    <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-xl bg-white border border-slate-100">
                                        <span class="text-[10px] font-bold text-slate-400 leading-none">{{ $req->request_date->format('d/m') }}</span>
                                        <span class="text-xs font-bold text-violet-600 leading-tight mt-0.5">{{ \Carbon\Carbon::parse($req->leave_time)->format('H:i') }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs text-slate-600 truncate" title="{{ $req->reason }}">{{ $req->reason }}</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $req->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <span class="inline-flex shrink-0 border px-2 py-0.5 rounded-full text-[10px] font-bold {{ $req->statusBadgeClass() }}">
                                        {{ $req->statusLabel() }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </aside>
        </div>
    </div>

    <script>
        (function () {
            const grace = {{ $grace }};
            const shiftSchedule = @json($shiftSchedule);
            const sessions = [
                { label: 'buổi sáng', end: '12:00' },
                { label: 'buổi chiều', end: '17:00' },
            ];
            const requestDateInput = document.getElementById('request_date');
            const dateHint = document.getElementById('request-date-hint');
            const leaveTimeInput = document.getElementById('leave_time');
            const timeHint = document.getElementById('leave-time-hint');
            const submitBtn = document.getElementById('early-leave-submit');
            const form = document.getElementById('early-leave-form');
            let hasShiftOnSelectedDate = true;

            function toMinutes(time) {
                const [h, m] = time.split(':').map(Number);
                return h * 60 + m;
            }

            function formatMinutes(total) {
                const h = Math.floor(total / 60);
                const m = total % 60;
                return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
            }

            function formatDateLabel(isoDate) {
                const [y, m, d] = isoDate.split('-');
                return d + '/' + m + '/' + y;
            }

            function updateDateHint() {
                const value = requestDateInput?.value;
                if (!value || !dateHint) {
                    return;
                }

                const shifts = shiftSchedule[value] || [];

                if (shifts.length === 0) {
                    hasShiftOnSelectedDate = false;
                    dateHint.className = 'mt-2 text-xs font-medium text-rose-600';
                    dateHint.textContent = 'Ngày ' + formatDateLabel(value) + ' không có ca làm. Vui lòng chọn ngày khác.';
                    dateHint.classList.remove('hidden');
                    requestDateInput.classList.add('border-rose-300', 'focus:border-rose-500', 'focus:ring-rose-500/20');
                    requestDateInput.classList.remove('border-slate-200', 'focus:border-violet-500', 'focus:ring-violet-500/20');
                } else {
                    hasShiftOnSelectedDate = true;
                    const shiftText = shifts.map(function (shift) {
                        return shift.name + ' (' + shift.start + ' – ' + shift.end + ')';
                    }).join(', ');
                    dateHint.className = 'mt-2 text-xs font-medium text-emerald-700';
                    dateHint.textContent = 'Ngày này có ca làm: ' + shiftText + '.';
                    dateHint.classList.remove('hidden');
                    requestDateInput.classList.remove('border-rose-300', 'focus:border-rose-500', 'focus:ring-rose-500/20');
                    requestDateInput.classList.add('border-slate-200', 'focus:border-violet-500', 'focus:ring-violet-500/20');
                }

                if (submitBtn) {
                    submitBtn.disabled = !hasShiftOnSelectedDate;
                }
            }

            function updateTimeHint() {
                const value = leaveTimeInput?.value;
                if (!value || !timeHint) {
                    timeHint?.classList.add('hidden');
                    return;
                }

                const leaveMin = toMinutes(value);
                let matched = sessions[0];

                for (const session of sessions) {
                    const endMin = toMinutes(session.end);
                    if (leaveMin <= endMin) {
                        matched = session;
                        break;
                    }
                    matched = session;
                }

                const endMin = toMinutes(matched.end);
                const graceStartMin = endMin - grace;
                const earlyMinutes = graceStartMin - leaveMin;

                if (earlyMinutes <= 0) {
                    timeHint.className = 'rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs leading-relaxed text-emerald-800';
                    timeHint.innerHTML = '<strong>Không cần lo phạt:</strong> Giờ bạn chọn nằm trong ' + grace + ' phút miễn trừ trước tan ' + matched.label + ' (' + formatMinutes(graceStartMin) + ' – ' + matched.end + '). Vẫn có thể gửi đơn nếu muốn ghi nhận chính thức.';
                } else {
                    timeHint.className = 'rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-relaxed text-amber-900';
                    timeHint.innerHTML = '<strong>Nên gửi đơn:</strong> Về lúc <strong>' + value + '</strong> (tan ca ' + matched.end + ') — nếu không có đơn duyệt, hệ thống có thể trừ lương khoảng <strong>' + earlyMinutes + ' phút</strong> (sau ' + grace + 'p miễn trừ).';
                }

                timeHint.classList.remove('hidden');
            }

            requestDateInput?.addEventListener('change', updateDateHint);
            requestDateInput?.addEventListener('input', updateDateHint);
            leaveTimeInput?.addEventListener('change', updateTimeHint);
            leaveTimeInput?.addEventListener('input', updateTimeHint);

            form?.addEventListener('submit', function (event) {
                updateDateHint();
                if (!hasShiftOnSelectedDate) {
                    event.preventDefault();
                }
            });

            updateDateHint();
            updateTimeHint();
        })();
    </script>

</x-dynamic-component>
