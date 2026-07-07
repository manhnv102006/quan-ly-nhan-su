<x-admin-layout title="Quản lý giao KPI">
    @php
        $statCards = [
            ['key' => 'pending', 'label' => 'Chờ phê duyệt', 'tone' => 'from-amber-400 to-orange-500', 'text' => 'text-amber-600', 'bg' => 'bg-amber-50'],
            ['key' => 'active', 'label' => 'Đang thực hiện', 'tone' => 'from-sky-400 to-blue-500', 'text' => 'text-sky-600', 'bg' => 'bg-sky-50'],
            ['key' => 'completed', 'label' => 'Hoàn thành', 'tone' => 'from-emerald-400 to-teal-500', 'text' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
            ['key' => 'total', 'label' => 'Tổng cộng', 'tone' => 'from-violet-500 to-indigo-600', 'text' => 'text-violet-600', 'bg' => 'bg-violet-50'],
        ];
    @endphp

    <div class="space-y-6">
        {{-- Hero --}}
        <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-900 via-violet-900 to-indigo-800 p-6 text-white shadow-xl shadow-violet-900/20 sm:p-8">
            <div class="absolute -right-16 top-0 h-48 w-48 rounded-full bg-violet-500/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-40 w-40 -translate-x-1/4 translate-y-1/4 rounded-full bg-indigo-400/15 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-200">Quản lý KPI</p>
                    <h2 class="mt-2 text-3xl font-extrabold tracking-tight">Giao KPI cho Manager</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-violet-100/90">
                        Phân bổ mục tiêu KPI, theo dõi tiến độ phê duyệt và quản lý chu kỳ đánh giá theo phòng ban.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-violet-200">Tổng bản ghi</p>
                        <p class="mt-1 text-2xl font-extrabold">{{ number_format($stats['total']) }}</p>
                    </div>
                    <a href="{{ route('admin.kpi-assignments.create') }}"
                       class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-bold text-violet-700 shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-violet-50">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Giao KPI mới
                    </a>
                </div>
            </div>
        </section>

        {{-- Stats --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($statCards as $card)
                <div class="admin-stat-card">
                    <div class="flex items-start justify-between">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br {{ $card['tone'] }} text-white shadow-lg">
                            <span class="text-sm font-bold">{{ number_format($stats[$card['key']]) }}</span>
                        </div>
                        <span class="rounded-full {{ $card['bg'] }} px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $card['text'] }}">
                            {{ $card['label'] }}
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($stats[$card['key']]) }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                </div>
            @endforeach
        </section>

        {{-- Filters --}}
        <section class="admin-card p-5 sm:p-6">
            <form method="GET" action="{{ route('admin.kpi-assignments.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Tìm kiếm</label>
                        <input type="text" name="search" placeholder="Mã KPI, tên, manager..."
                            value="{{ request('search') }}"
                            class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-violet-500/30">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Trạng thái</label>
                        <select name="status" class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-violet-500/30">
                            <option value="">Tất cả trạng thái</option>
                            @foreach (['pending' => 'Chờ phê duyệt', 'active' => 'Đang thực hiện', 'completed' => 'Hoàn thành', 'cancelled' => 'Hủy'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">KPI</label>
                        <select name="kpi_id" class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-violet-500/30">
                            <option value="">Tất cả KPI</option>
                            @foreach ($kpis as $kpi)
                                <option value="{{ $kpi->id }}" @selected((string) request('kpi_id') === (string) $kpi->id)>
                                    {{ $kpi->code }} — {{ $kpi->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Manager</label>
                        <select name="manager_id" class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-violet-500/30">
                            <option value="">Tất cả manager</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}" @selected((string) request('manager_id') === (string) $manager->id)>
                                    {{ $manager->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Lọc kết quả
                    </button>
                    <a href="{{ route('admin.kpi-assignments.index') }}" class="inline-flex items-center rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200">
                        Xóa bộ lọc
                    </a>
                </div>
            </form>
        </section>

        {{-- Table --}}
        <section class="admin-card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Danh sách</p>
                    <h3 class="mt-1 text-lg font-bold text-slate-800">Bản ghi giao KPI</h3>
                </div>
                <p class="text-sm text-slate-500">
                    Hiển thị <span class="font-semibold text-slate-700">{{ $assignments->count() }}</span> / {{ number_format($assignments->total()) }} bản ghi
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80 text-left">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-400">#</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-400">KPI</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-400">Manager</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wide text-slate-400">Mục tiêu</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wide text-slate-400">Thời gian</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wide text-slate-400">Trạng thái</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wide text-slate-400">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($assignments as $key => $assignment)
                            @php
                                $managerDeleted = $assignment->manager?->trashed();
                                $progress = $assignment->target_progress;
                                $circumference = 2 * 3.14159 * 18;
                                $dashOffset = $circumference - ($progress / 100) * $circumference;
                            @endphp
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="px-6 py-5 text-xs font-medium text-slate-400">
                                    {{ ($assignments->currentPage() - 1) * $assignments->perPage() + $key + 1 }}
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-[11px] font-bold text-white shadow-sm">
                                            {{ $assignment->kpi?->code ? strtoupper(substr($assignment->kpi->code, 0, 3)) : 'KPI' }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-800">{{ $assignment->kpi_title }}</p>
                                            <p class="mt-0.5 text-xs text-slate-500">
                                                {{ $assignment->kpi?->code ?? '—' }}
                                                @if ($assignment->kpi?->unit)
                                                    · ĐVT: {{ $assignment->kpi->unit }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $managerDeleted ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }} text-xs font-bold">
                                            {{ strtoupper(mb_substr($assignment->manager?->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800">{{ $assignment->manager?->name ?? '—' }}</p>
                                            @if ($managerDeleted)
                                                <span class="mt-0.5 inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">Tài khoản đã xóa</span>
                                            @else
                                                <p class="text-xs text-slate-500">{{ $assignment->manager?->email ?? '—' }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex justify-center">
                                        @if ($assignment->is_percent_target)
                                            <div class="relative flex h-[4.5rem] w-[4.5rem] items-center justify-center">
                                                <svg class="-rotate-90 transform" width="72" height="72" viewBox="0 0 72 72">
                                                    <circle cx="36" cy="36" r="18" fill="none" stroke="#e2e8f0" stroke-width="5"></circle>
                                                    <circle cx="36" cy="36" r="18" fill="none" stroke="url(#targetGradient{{ $assignment->id }})" stroke-width="5" stroke-linecap="round"
                                                        stroke-dasharray="{{ $circumference }}"
                                                        stroke-dashoffset="{{ $dashOffset }}"></circle>
                                                    <defs>
                                                        <linearGradient id="targetGradient{{ $assignment->id }}" x1="0%" y1="0%" x2="100%" y2="100%">
                                                            <stop offset="0%" stop-color="#8b5cf6"></stop>
                                                            <stop offset="100%" stop-color="#6366f1"></stop>
                                                        </linearGradient>
                                                    </defs>
                                                </svg>
                                                <span class="absolute text-sm font-extrabold text-violet-700">{{ $assignment->target_short }}%</span>
                                            </div>
                                        @else
                                            <div class="min-w-[5.5rem] rounded-2xl border border-violet-100 bg-violet-50 px-4 py-3 text-center">
                                                <p class="text-lg font-extrabold leading-none text-violet-700">{{ $assignment->target_short }}</p>
                                                <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-violet-500">{{ $assignment->target_unit }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="space-y-1 text-sm">
                                        <p class="font-medium text-slate-700">
                                            {{ $assignment->start_date->format('d/m/Y') }}
                                            <span class="text-slate-400">→</span>
                                            {{ $assignment->end_date->format('d/m/Y') }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ $assignment->start_date->diffInDays($assignment->end_date) + 1 }} ngày
                                            · Giao {{ $assignment->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $assignment->status_tailwind }}">
                                        {{ $assignment->status_label }}
                                    </span>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap items-center justify-center gap-1.5">
                                        @if ($assignment->status === 'pending')
                                            <form action="{{ route('admin.kpi-assignments.approve', $assignment) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" title="Phê duyệt"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                    </svg>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.kpi-assignments.reject', $assignment) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" title="Từ chối"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition hover:bg-rose-100">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @elseif ($assignment->status === 'active')
                                            <form action="{{ route('admin.kpi-assignments.complete', $assignment) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" title="Hoàn thành"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600 transition hover:bg-sky-100">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('admin.kpi-assignments.edit', $assignment) }}" title="Sửa"
                                           class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition hover:bg-amber-100">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                            </svg>
                                        </a>

                                        <form action="{{ route('admin.kpi-assignments.destroy', $assignment) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Bạn chắc chắn muốn xóa bản ghi giao KPI này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Xóa"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-rose-50 hover:text-rose-600">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <p class="text-4xl">📊</p>
                                        <p class="mt-3 text-lg font-semibold text-slate-700">Chưa có bản ghi giao KPI</p>
                                        <p class="mt-1 text-sm text-slate-500">Bắt đầu bằng cách giao KPI cho manager phụ trách.</p>
                                        <a href="{{ route('admin.kpi-assignments.create') }}"
                                           class="mt-4 inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700">
                                            Giao KPI ngay
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($assignments->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                    {{ $assignments->withQueryString()->links() }}
                </div>
            @endif
        </section>
    </div>
</x-admin-layout>
