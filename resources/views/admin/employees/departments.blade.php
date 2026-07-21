<x-admin-layout title="Quản lý nhân viên">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Quản lý nhân viên</h2>
                <p class="text-sm text-slate-500 mt-1">Chọn phòng ban để xem và thêm nhân viên</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.employees', ['view' => 'all']) }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                    Xem tất cả nhân viên
                </a>
                <a href="{{ route('admin.employees.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                    + Thêm nhân viên
                </a>
                <a href="{{ route('admin.employees.trash') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                    Nhân viên đã xóa
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="flex items-center gap-3 bg-white border border-emerald-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 bg-white border border-red-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Tổng nhân viên</p>
                <h3 class="text-3xl font-bold mt-2 text-slate-900">{{ $stats['total'] }}</h3>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Đang làm việc</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">{{ $stats['active'] }}</h3>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Tạm khóa</p>
                <h3 class="text-3xl font-bold mt-2 text-amber-600">{{ $stats['inactive'] }}</h3>
            </div>
        </div>

        <div>
            <h3 class="mb-3 font-semibold text-slate-800">Phòng ban ({{ $departments->count() }})</h3>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($departments as $department)
                    @php
                        $count = (int) $department->employees_count;
                        $limit = $department->maxEmployeesLimit();
                        $percent = $limit > 0 ? min(100, (int) round($count / $limit * 100)) : 0;
                        $isFull = $count >= $limit;
                    @endphp
                    <div class="group flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-violet-300 hover:shadow-md">
                        <a href="{{ route('admin.employees', ['department_id' => $department->id]) }}" class="flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-lg font-bold text-violet-700">
                                    {{ mb_strtoupper(mb_substr($department->department_name, 0, 1)) }}
                                </div>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $department->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $department->status === 'active' ? 'Hoạt động' : 'Ngừng' }}
                                </span>
                            </div>

                            <h4 class="mt-4 font-bold text-slate-800 group-hover:text-violet-700 transition">{{ $department->department_name }}</h4>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $department->department_code ?? '—' }}</p>

                            <p class="mt-3 text-sm text-slate-500">
                                Quản lý: <span class="font-medium text-slate-700">{{ $department->manager?->full_name ?? 'Chưa có' }}</span>
                            </p>

                            <div class="mt-4">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500">Sĩ số</span>
                                    <span class="font-semibold {{ $isFull ? 'text-rose-600' : 'text-slate-700' }}">{{ $count }}/{{ $limit }}</span>
                                </div>
                                <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $isFull ? 'bg-rose-500' : 'bg-violet-500' }}" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        </a>

                        <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-4">
                            <a href="{{ route('admin.employees', ['department_id' => $department->id]) }}"
                               class="flex-1 rounded-xl bg-slate-100 px-3 py-2 text-center text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                                Xem nhân viên
                            </a>
                            <a href="{{ route('admin.employees.create', ['department_id' => $department->id]) }}"
                               class="rounded-xl bg-violet-600 px-3 py-2 text-center text-sm font-medium text-white transition hover:bg-violet-700 {{ $isFull ? 'pointer-events-none opacity-50' : '' }}"
                               title="{{ $isFull ? 'Phòng ban đã đầy' : 'Thêm nhân viên vào phòng ban này' }}">
                                + Thêm
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center text-slate-400">
                        Chưa có phòng ban nào
                    </div>
                @endforelse

                @if ($unassignedCount > 0)
                    <div class="flex flex-col rounded-2xl border border-dashed border-amber-300 bg-amber-50/40 p-5 shadow-sm">
                        <a href="{{ route('admin.employees', ['department_id' => 'none']) }}" class="flex-1">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-lg font-bold text-amber-700">?</div>
                            <h4 class="mt-4 font-bold text-slate-800">Chưa có phòng ban</h4>
                            <p class="mt-0.5 text-xs text-slate-400">Nhân viên chưa được gán phòng ban</p>
                            <p class="mt-3 text-sm text-slate-500">Số lượng: <span class="font-semibold text-amber-700">{{ $unassignedCount }}</span></p>
                        </a>
                        <div class="mt-4 border-t border-amber-100 pt-4">
                            <a href="{{ route('admin.employees', ['department_id' => 'none']) }}"
                               class="block rounded-xl bg-amber-500 px-3 py-2 text-center text-sm font-medium text-white transition hover:bg-amber-600">
                                Xem danh sách
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

</x-admin-layout>
