@php
    use App\Models\OvertimeRequest;
    use App\Support\TimeInput;

    $showDepartmentColumn = $showDepartmentColumn ?? true;
    $scopeLabel = $scopeLabel ?? 'Toàn công ty';
    $statusClasses = OvertimeRequest::STATUS_TAILWIND_CLASSES;
    $statusLabels = OvertimeRequest::STATUS_LABELS;
    $displayHours = fn ($item) => number_format(abs((float) $item->total_hours), 2);
    $tableColspan = $showDepartmentColumn ? 9 : 8;
@endphp

<section class="space-y-6">
    <div>
        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Tổng hợp</p>
        <h2 class="mt-1 text-lg font-bold text-slate-800">{{ $scopeLabel }}</h2>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Tổng đơn</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Chờ duyệt</p>
            <p class="mt-1 text-3xl font-extrabold text-amber-600">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Đã duyệt</p>
            <p class="mt-1 text-3xl font-extrabold text-emerald-600">{{ number_format($stats['approved']) }}</p>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Từ chối</p>
            <p class="mt-1 text-3xl font-extrabold text-rose-600">{{ number_format($stats['rejected']) }}</p>
        </div>
        <div class="rounded-2xl border border-sky-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Hoàn thành</p>
            <p class="mt-1 text-3xl font-extrabold text-sky-600">{{ number_format($stats['completed']) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-5">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-indigo-600">Danh sách</p>
            <h3 class="mt-1 text-lg font-bold text-slate-800">Đơn tăng ca — {{ $scopeLabel }}</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[960px] text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/80 text-left">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-400">#</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-400">Nhân viên</th>
                        @if ($showDepartmentColumn)
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-400">Phòng ban</th>
                        @endif
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-400">Ngày tăng ca</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-400">Khung giờ</th>
                        <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wide text-slate-400">Tổng giờ</th>
                        <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wide text-slate-400">Trạng thái</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-400">Ngày tạo</th>
                        <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wide text-slate-400">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($overtimeRequests as $index => $item)
                        <tr class="transition hover:bg-slate-50/60">
                            <td class="px-6 py-4 text-xs font-medium text-slate-400">
                                {{ ($overtimeRequests->firstItem() ?? 0) + $index }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 text-xs font-bold text-white shadow-sm">
                                        {{ strtoupper(mb_substr($item->employee?->full_name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $item->employee?->full_name ?? '—' }}</p>
                                        <p class="text-xs text-slate-500">{{ $item->employee?->employee_code ?? '—' }}</p>
                                        @if ($item->employee?->hasManagerRole())
                                            <span class="mt-1 inline-flex rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-violet-600">Quản lý</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @if ($showDepartmentColumn)
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $item->employee?->department?->department_name ?? '—' }}
                                </td>
                            @endif
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 font-medium text-slate-700">
                                    <i class="bi bi-calendar3 text-slate-400"></i>
                                    {{ optional($item->work_date)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                                    <i class="bi bi-clock text-violet-500"></i>
                                    {{ TimeInput::forInput($item->start_time) }}
                                    <span class="text-slate-400">→</span>
                                    {{ TimeInput::forInput($item->end_time) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex min-w-[3.5rem] items-center justify-center rounded-full bg-violet-50 px-3 py-1 text-sm font-bold text-violet-700">
                                    {{ $displayHours($item) }}h
                                </span>
                                <div class="text-[10px] text-slate-500 font-normal mt-1">Hệ số: x{{ $item->rate_multiplier ?: 1.5 }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClasses[$item->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                    {{ $statusLabels[$item->status] ?? $item->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ optional($item->created_at)->format('d/m/Y') }}
                                <span class="block text-[11px] text-slate-400">{{ optional($item->created_at)->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap items-center justify-center gap-1.5">
                                    @if ($item->isPending())
                                        <form method="POST" action="{{ route('admin.overtime-requests.approve', $item) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700"
                                                    onclick="return confirm('Duyệt đơn tăng ca của {{ $item->employee?->full_name }}?')">
                                                <i class="bi bi-check-lg"></i> Duyệt
                                            </button>
                                        </form>
                                        <button type="button"
                                                onclick="openOvertimeRejectModal('{{ route('admin.overtime-requests.reject', $item) }}', @js($item->employee?->full_name))"
                                                class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-700">
                                            <i class="bi bi-x-lg"></i> Từ chối
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.overtime-requests.show', $item) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                                       title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.overtime-requests.edit', $item) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100"
                                       title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.overtime-requests.destroy', $item) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100"
                                                title="Xóa"
                                                onclick="return confirm('Bạn có chắc muốn xóa đơn này?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $tableColspan }}" class="px-6 py-16 text-center text-slate-500">
                                Chưa có đơn tăng ca trong phạm vi này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($overtimeRequests->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $overtimeRequests->links() }}
            </div>
        @endif
    </div>
</section>

<div id="overtime-reject-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
    <div class="mx-4 w-full max-w-md rounded-3xl bg-white p-6 shadow-xl">
        <h3 class="mb-2 text-lg font-bold text-slate-800">Từ chối đơn tăng ca</h3>
        <p class="mb-4 text-sm text-slate-500">
            Nhập lý do từ chối cho nhân viên <strong id="overtime-reject-name" class="text-slate-800"></strong>:
        </p>
        <form id="overtime-reject-form" action="" method="POST">
            @csrf
            @method('PATCH')
            <div class="mb-5">
                <label for="overtime_reject_reason" class="mb-2 block text-sm font-semibold text-slate-700">Lý do từ chối</label>
                <textarea id="overtime_reject_reason" name="reject_reason" required rows="3"
                          placeholder="Nhập lý do từ chối..."
                          class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeOvertimeRejectModal()"
                        class="flex-1 rounded-xl bg-slate-100 px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                    Hủy
                </button>
                <button type="submit"
                        class="flex-1 rounded-xl bg-rose-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-rose-700">
                    Xác nhận từ chối
                </button>
            </div>
        </form>
    </div>
</div>

@once
    @push('scripts')
    <script>
        function openOvertimeRejectModal(actionUrl, employeeName) {
            const modal = document.getElementById('overtime-reject-modal');
            const form = document.getElementById('overtime-reject-form');
            document.getElementById('overtime-reject-name').textContent = employeeName || '';
            form.setAttribute('action', actionUrl);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeOvertimeRejectModal() {
            const modal = document.getElementById('overtime-reject-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
    @endpush
@endonce
