@php
    use App\Models\OvertimeRequest;
    use App\Support\TimeInput;

    $statusClasses = OvertimeRequest::STATUS_TAILWIND_CLASSES;
    $statusLabels = OvertimeRequest::STATUS_LABELS;
    $statusSelectClasses = [
        OvertimeRequest::STATUS_PENDING => 'border-amber-200 bg-amber-50 text-amber-800 focus:ring-amber-300',
        OvertimeRequest::STATUS_APPROVED => 'border-emerald-200 bg-emerald-50 text-emerald-800 focus:ring-emerald-300',
        OvertimeRequest::STATUS_REJECTED => 'border-rose-200 bg-rose-50 text-rose-800 focus:ring-rose-300',
        OvertimeRequest::STATUS_COMPLETED => 'border-sky-200 bg-sky-50 text-sky-800 focus:ring-sky-300',
    ];

    $displayHours = fn ($item) => number_format(abs((float) $item->total_hours), 2);
@endphp

<x-admin-layout title="Danh sách đơn tăng ca">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Quản lý chấm công</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-800">Danh sách đơn tăng ca</h2>
                <p class="mt-1 text-sm text-slate-500">Theo dõi, duyệt và cập nhật trạng thái trực tiếp trên bảng.</p>
            </div>
            <a href="{{ route('admin.overtime-requests.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-violet-900/20 transition hover:bg-violet-700">
                <i class="bi bi-plus-lg"></i>
                Tạo đơn
            </a>
        </div>

        <x-flash-messages />

        {{-- Stats --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                        <i class="bi bi-collection text-lg"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tổng</span>
                </div>
                <p class="mt-4 text-3xl font-extrabold text-slate-800">{{ number_format($stats['total']) }}</p>
                <p class="mt-1 text-sm text-slate-500">Tổng đơn</p>
            </div>

            <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                        <i class="bi bi-hourglass-split text-lg"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Chờ</span>
                </div>
                <p class="mt-4 text-3xl font-extrabold text-amber-600">{{ number_format($stats['pending']) }}</p>
                <p class="mt-1 text-sm text-slate-500">Chờ duyệt</p>
            </div>

            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                        <i class="bi bi-check-circle text-lg"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Duyệt</span>
                </div>
                <p class="mt-4 text-3xl font-extrabold text-emerald-600">{{ number_format($stats['approved']) }}</p>
                <p class="mt-1 text-sm text-slate-500">Đã duyệt</p>
            </div>

            <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                        <i class="bi bi-x-circle text-lg"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Từ chối</span>
                </div>
                <p class="mt-4 text-3xl font-extrabold text-rose-600">{{ number_format($stats['rejected']) }}</p>
                <p class="mt-1 text-sm text-slate-500">Đã từ chối</p>
            </div>

            <div class="rounded-2xl border border-sky-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-100 text-sky-600">
                        <i class="bi bi-flag text-lg"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-sky-600">Xong</span>
                </div>
                <p class="mt-4 text-3xl font-extrabold text-sky-600">{{ number_format($stats['completed']) }}</p>
                <p class="mt-1 text-sm text-slate-500">Hoàn thành</p>
            </div>
        </section>

        {{-- Table --}}
        <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-indigo-600">Danh sách</p>
                <h3 class="mt-1 text-lg font-bold text-slate-800">Đơn tăng ca nhân viên</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80 text-left">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-400">#</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-400">Nhân viên</th>
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
                                        </div>
                                    </div>
                                </td>
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
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST"
                                          action="{{ route('admin.overtime-requests.status', $item) }}"
                                          class="overtime-status-form mx-auto w-fit"
                                          data-current="{{ $item->status }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="reject_reason" value="">
                                        <select name="status"
                                                class="overtime-status-select cursor-pointer rounded-xl border px-3 py-2 text-xs font-bold shadow-sm outline-none transition focus:ring-2 {{ $statusSelectClasses[$item->status] ?? 'border-slate-200 bg-white text-slate-700 focus:ring-violet-300' }}"
                                                aria-label="Trạng thái đơn tăng ca">
                                            @foreach ($statusLabels as $value => $label)
                                                <option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </form>
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
                                                        onclick="return confirm('Duyệt đơn tăng ca này?')">
                                                    <i class="bi bi-check-lg"></i> Duyệt
                                                </button>
                                            </form>
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
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <i class="bi bi-clock-history text-3xl"></i>
                                        </div>
                                        <p class="font-semibold text-slate-700">Chưa có đơn tăng ca</p>
                                        <p class="mt-1 text-sm text-slate-500">Tạo đơn mới cho nhân viên, phòng ban hoặc toàn công ty.</p>
                                        <a href="{{ route('admin.overtime-requests.create') }}"
                                           class="mt-4 inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">
                                            <i class="bi bi-plus-lg"></i> Tạo đơn đầu tiên
                                        </a>
                                    </div>
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
        </section>
    </div>

    @push('scripts')
    <script>
        const statusSelectClasses = @json($statusSelectClasses);
        const defaultSelectClass = 'border-slate-200 bg-white text-slate-700 focus:ring-violet-300';

        document.querySelectorAll('.overtime-status-select').forEach(function (select) {
            select.addEventListener('change', function () {
                const form = this.closest('.overtime-status-form');
                const current = form.dataset.current;
                const next = this.value;

                if (next === current) {
                    return;
                }

                if (next === 'rejected') {
                    const reason = prompt('Nhập lý do từ chối:');
                    if (!reason || !reason.trim()) {
                        this.value = current;
                        return;
                    }
                    form.querySelector('[name="reject_reason"]').value = reason.trim();
                } else {
                    form.querySelector('[name="reject_reason"]').value = '';
                }

                if (!confirm('Cập nhật trạng thái đơn tăng ca này?')) {
                    this.value = current;
                    return;
                }

                form.submit();
            });
        });
    </script>
    @endpush
</x-admin-layout>
