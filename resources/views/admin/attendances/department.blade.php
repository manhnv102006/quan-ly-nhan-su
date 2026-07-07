<x-admin-layout title="Chấm công — {{ $department->department_name }}">
    <div class="space-y-6">

        {{-- Breadcrumb + header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('admin.attendances') }}"
                   class="inline-flex items-center gap-1 text-sm font-semibold text-violet-600 hover:text-violet-800 transition mb-2">
                    ← Quay lại danh sách phòng ban
                </a>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Chấm công</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-800">{{ $department->department_name }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Danh sách nhân viên · tháng {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}
                </p>
            </div>
        </div>

        {{-- Tìm kiếm nhân viên --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <form action="{{ route('admin.attendances.department', $department) }}" method="GET"
                  class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">
                        Tìm nhân viên
                    </label>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Tên hoặc mã nhân viên…"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition
                                  placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                </div>
                <button type="submit"
                        class="inline-flex items-center rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold
                               text-white shadow-md shadow-violet-500/20 transition hover:bg-violet-700">
                    Tìm kiếm
                </button>
                @if($search)
                    <a href="{{ route('admin.attendances.department', $department) }}"
                       class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5
                              text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Xóa lọc
                    </a>
                @endif
            </form>
        </div>

        {{-- Danh sách nhân viên --}}
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h3 class="text-sm font-bold text-slate-800">Danh sách nhân viên</h3>
                <p class="text-xs text-slate-500">
                    {{ $employees->total() }} nhân viên · Nhấn vào nhân viên để xem chi tiết chấm công
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">STT</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Mã NV</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Họ và tên</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Chức vụ</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Ngày làm<br><span class="font-normal normal-case text-[10px]">tháng {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</span></th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Đi muộn</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Vắng</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Tổng giờ</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Chấm công gần nhất</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $index => $emp)
                            @php
                                $st        = $statsMap[$emp->id] ?? null;
                                $lastDate  = $lastDateMap[$emp->id] ?? null;
                                $present   = $st ? (int)$st->present : 0;
                                $late      = $st ? (int)$st->late    : 0;
                                $absent    = $st ? (int)$st->absent  : 0;
                                $totHours  = $st ? round($st->total_hours, 1) : 0;
                            @endphp
                            <tr class="border-t transition hover:bg-violet-50/40">
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ $employees->firstItem() + $index }}
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-500">
                                    {{ $emp->employee_code }}
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-800">{{ $emp->full_name }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ $emp->position?->position_name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-bold text-emerald-600">{{ $present + $late }}</span>
                                    <span class="text-xs text-slate-400"> ngày</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($late > 0)
                                        <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                            {{ $late }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($absent > 0)
                                        <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-700">
                                            {{ $absent }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-medium text-slate-700">
                                    {{ $totHours > 0 ? $totHours . ' giờ' : '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ $lastDate ? \Carbon\Carbon::parse($lastDate)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.attendances.employee', [$department, $emp]) }}"
                                       class="inline-flex items-center gap-1 rounded-xl bg-violet-600 px-4 py-1.5
                                              text-xs font-semibold text-white transition hover:bg-violet-700">
                                        Xem chấm công
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-14 text-center">
                                    <p class="text-sm font-semibold text-slate-600">Không có nhân viên nào trong phòng ban này.</p>
                                    @if($search)
                                        <a href="{{ route('admin.attendances.department', $department) }}"
                                           class="mt-2 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                                            Xóa bộ lọc
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employees->hasPages())
                <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>

    </div>
</x-admin-layout>
