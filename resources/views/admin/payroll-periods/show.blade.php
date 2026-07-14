<x-admin-layout title="Chi tiết kỳ lương">

    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.payroll-periods.index') }}" 
                       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition"
                       title="Quay lại danh sách kỳ lương">
                        ←
                    </a>
                    <h2 class="text-2xl font-bold text-slate-800">Chi tiết kỳ lương: {{ $payrollPeriod->name }}</h2>
                </div>
                <p class="text-sm text-slate-500 mt-1.5 ml-12">
                    Thời gian: từ {{ $payrollPeriod->start_date?->format('d/m/Y') }} đến {{ $payrollPeriod->end_date?->format('d/m/Y') }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                @if ($payrollPeriod->status === 'open')
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                        🔵 Chưa tính lương (Open)
                    </span>
                @elseif ($payrollPeriod->status === 'calculated')
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                        🟡 Đã tính lương (Calculated)
                    </span>
                @elseif ($payrollPeriod->status === 'approved')
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-violet-100 text-violet-700 border border-violet-200">
                        🟣 Đã duyệt (Approved)
                    </span>
                @elseif ($payrollPeriod->status === 'paid')
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                        🟢 Đã chi trả (Paid)
                    </span>
                @else
                    <span class="inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        🔒 Đã đóng (Closed)
                    </span>
                @endif
            </div>
        </div>

        @if($payrollPeriod->approved_by || $payrollPeriod->paid_by)
            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex flex-wrap gap-6 text-xs text-slate-500">
                @if($payrollPeriod->approved_by)
                    <div class="flex items-center gap-1.5">
                        <span>✔️ <b>Người duyệt:</b> {{ $payrollPeriod->approver?->name }}</span>
                        <span>•</span>
                        <span>{{ $payrollPeriod->approved_at?->format('H:i d/m/Y') }}</span>
                    </div>
                @endif
                @if($payrollPeriod->paid_by)
                    <div class="flex items-center gap-1.5">
                        <span>💵 <b>Người chi trả:</b> {{ $payrollPeriod->payer?->name }}</span>
                        <span>•</span>
                        <span>{{ $payrollPeriod->paid_at?->format('H:i d/m/Y') }}</span>
                    </div>
                @endif
            </div>
        @endif

        <!-- Thống kê tổng quan -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Tổng phiếu lương</p>
                <h3 class="text-3xl font-bold mt-2 text-slate-800">{{ $stats['total_count'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Tổng lương thực lĩnh</p>
                <h3 class="text-3xl font-bold mt-2 text-violet-600">
                    {{ number_format($stats['total_salary'], 0, ',', '.') }} ₫
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Đã chi trả (Paid)</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">
                    {{ number_format($stats['paid_salary'], 0, ',', '.') }} ₫
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Chưa chi trả (Unpaid)</p>
                <h3 class="text-3xl font-bold mt-2 text-rose-600">
                    {{ number_format($stats['unpaid_salary'], 0, ',', '.') }} ₫
                </h3>
            </div>
        </div>

        <!-- Phân chia theo phòng ban -->
        <div class="mt-8">
            @include('admin.partials.department-cards', [
                'departmentSummaries' => $departmentSummaries,
                'routeName' => 'admin.payroll-periods.department',
                'routeParams' => ['payrollPeriod' => $payrollPeriod->id],
                'statLabels' => ['Tổng số nhân viên', 'Tổng số lương', 'Trạng thái'],
                'statKeys' => ['employee_count', 'total_salary', 'status_label'],
                'statTones' => ['slate', 'violet', 'sky'],
                'formatters' => [
                    'total_salary' => fn($val) => number_format($val, 0, ',', '.') . ' ₫',
                    'status_label' => fn($val) => $val
                ]
            ])
        </div>

        <!-- Lịch sử hoạt động -->
        <div class="mt-8">
            <h3 class="text-xl font-bold text-slate-800 mb-4">Lịch sử thay đổi (Activity Log)</h3>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                @if(isset($activities) && $activities->count() > 0)
                    <div class="relative border-l-2 border-slate-200 ml-4 space-y-8 py-2">
                        @foreach($activities as $activity)
                            <div class="relative pl-6">
                                <!-- Điểm mốc (dot) -->
                                <div class="absolute -left-[11px] top-1.5 w-5 h-5 rounded-full bg-violet-500 border-4 border-white shadow-sm"></div>
                                
                                <div class="flex items-start gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-slate-800">{{ $activity->causer ? $activity->causer->name : 'Hệ thống' }}</span>
                                            <span class="text-slate-400 text-sm">•</span>
                                            <span class="text-slate-500 text-sm" title="{{ $activity->created_at?->format('d/m/Y H:i:s') }}">
                                                {{ $activity->created_at?->diffForHumans() }}
                                            </span>
                                        </div>
                                        
                                        <div class="mt-1 text-slate-700">
                                            @if($activity->event === 'created')
                                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-emerald-50 text-emerald-700 text-xs font-semibold">✨ Đã tạo kỳ lương</span>
                                            @elseif($activity->event === 'updated')
                                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-amber-50 text-amber-700 text-xs font-semibold">📝 Đã cập nhật</span>
                                                @if($activity->properties->has('attributes') && $activity->properties->has('old'))
                                                    <div class="mt-2 text-sm bg-slate-50 border border-slate-100 rounded-xl p-3">
                                                        <ul class="space-y-1">
                                                            @foreach($activity->properties['attributes'] as $key => $newValue)
                                                                @php
                                                                    $oldValue = $activity->properties['old'][$key] ?? 'N/A';
                                                                @endphp
                                                                @if($oldValue != $newValue)
                                                                    <li class="flex items-center gap-2">
                                                                        <span class="font-medium text-slate-600 w-24">{{ $key }}:</span> 
                                                                        <span class="text-rose-500 line-through bg-rose-50 px-1 rounded">{{ is_bool($oldValue) ? ($oldValue ? 'true' : 'false') : $oldValue }}</span> 
                                                                        <span class="text-slate-400">→</span> 
                                                                        <span class="text-emerald-600 font-medium bg-emerald-50 px-1 rounded">{{ is_bool($newValue) ? ($newValue ? 'true' : 'false') : $newValue }}</span>
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            @elseif($activity->event === 'deleted')
                                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-rose-50 text-rose-700 text-xs font-semibold">🗑️ Đã xóa</span>
                                            @elseif($activity->event === 'calculate')
                                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-violet-50 text-violet-700 text-xs font-semibold">⚡ {{ $activity->description }}</span>
                                            @elseif($activity->event === 'recalculate')
                                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-orange-50 text-orange-700 text-xs font-semibold">🔄 {{ $activity->description }}</span>
                                            @elseif($activity->event === 'approve')
                                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-indigo-50 text-indigo-700 text-xs font-semibold">✅ {{ $activity->description }}</span>
                                            @elseif($activity->event === 'pay')
                                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-emerald-50 text-emerald-700 text-xs font-semibold">💰 {{ $activity->description }}</span>
                                            @elseif($activity->event === 'close')
                                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-slate-100 text-slate-700 text-xs font-semibold">🔒 {{ $activity->description }}</span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-slate-100 text-slate-600 text-xs font-semibold">{{ $activity->description }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <span class="text-2xl">📜</span>
                        </div>
                        <p class="text-slate-500 font-medium">Chưa có lịch sử thay đổi nào được ghi nhận.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Thông báo Success -->
    @if (session('success'))
        <div id="success-toast"
             class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-emerald-200 shadow-lg rounded-2xl px-5 py-4 max-w-sm">
            <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Thông báo Error -->
    @if (session('error'))
        <div id="error-toast"
             class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-red-200 shadow-lg rounded-2xl px-5 py-4 max-w-sm">
            <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">{{ session('error') }}</p>
        </div>
    @endif

    <script>
        // Tự tắt Toast thông báo sau 4 giây
        const successToast = document.getElementById('success-toast');
        if (successToast) {
            setTimeout(function () {
                successToast.style.transition = 'opacity 0.3s ease';
                successToast.style.opacity = '0';
                setTimeout(function () { successToast.remove(); }, 300);
            }, 4000);
        }

        const errorToast = document.getElementById('error-toast');
        if (errorToast) {
            setTimeout(function () {
                errorToast.style.transition = 'opacity 0.3s ease';
                errorToast.style.opacity = '0';
                setTimeout(function () { errorToast.remove(); }, 300);
            }, 4000);
        }
    </script>

</x-admin-layout>
