<x-admin-layout title="Quản lý giao KPI">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">

            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Danh sách giao KPI
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Tổng cộng {{ $assignments->total() }} bản ghi
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.kpi-assignments.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                    <span>+</span>
                    Giao KPI
                </a>
            </div>

        </div>

        {{-- Filter Form --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">

            <form method="GET" action="{{ route('admin.kpi-assignments.index') }}" class="space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Tìm kiếm</label>
                        <input type="text" name="search" placeholder="Mã KPI, tên KPI, manager..."
                            value="{{ request('search') }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Trạng thái</label>
                        <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                            <option value="">Tất cả</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ phê duyệt</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang thực hiện</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Hủy</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">KPI</label>
                        <select name="kpi_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                            <option value="">Tất cả</option>
                            @foreach($kpis as $kpi)
                                <option value="{{ $kpi->id }}" {{ request('kpi_id') == $kpi->id ? 'selected' : '' }}>
                                    {{ $kpi->code }} - {{ $kpi->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Manager</label>
                        <select name="manager_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                            <option value="">Tất cả</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ request('manager_id') == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition">
                        Tìm kiếm
                    </button>
                    <a href="{{ route('admin.kpi-assignments.index') }}" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition">
                        Xóa lọc
                    </a>
                </div>

            </form>

        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Chờ phê duyệt</p>
                <h3 class="text-3xl font-bold mt-2 text-yellow-600">
                    {{ number_format($stats['pending']) }}
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Đang thực hiện</p>
                <h3 class="text-3xl font-bold mt-2 text-blue-600">
                    {{ number_format($stats['active']) }}
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Hoàn thành</p>
                <h3 class="text-3xl font-bold mt-2 text-green-600">
                    {{ number_format($stats['completed']) }}
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Tổng cộng</p>
                <h3 class="text-3xl font-bold mt-2 text-violet-600">
                    {{ number_format($stats['total']) }}
                </h3>
            </div>

        </div>

        {{-- Table --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800">
                    Danh sách giao KPI
                </h3>
            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">STT</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mã KPI</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tên KPI</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Manager</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Target</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Ngày bắt đầu</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Ngày kết thúc</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($assignments as $key => $assignment)

                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ ($assignments->currentPage() - 1) * $assignments->perPage() + $key + 1 }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $assignment->kpi_code }}
                            </td>

                            <td class="px-6 py-4 text-slate-700">
                                {{ $assignment->kpi_title }}
                            </td>

                            <td class="px-6 py-4 text-slate-700">
                                {{ $assignment->manager_name }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100 text-blue-700 font-semibold text-sm">
                                    {{ $assignment->target }}%
                                </span>
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $assignment->start_date->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $assignment->end_date->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-block px-3 py-1 rounded-lg text-xs font-medium {{ $assignment->status_color }}">
                                    {{ $assignment->status_label }}
                                </span>
                            </td>

                            <td class="px-6 py-4">

                                <div class="flex justify-center gap-1">

                                    @if($assignment->status == 'pending')
                                        <form action="{{ route('admin.kpi-assignments.approve', $assignment) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="w-9 h-9 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition" title="Phê duyệt">
                                                ✓
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.kpi-assignments.reject', $assignment) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="w-9 h-9 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition" title="Từ chối">
                                                ✕
                                            </button>
                                        </form>
                                    @elseif($assignment->status == 'active')
                                        <form action="{{ route('admin.kpi-assignments.complete', $assignment) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="Hoàn thành">
                                                ◐
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.kpi-assignments.edit', $assignment) }}"
                                        class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 hover:bg-amber-200 transition flex items-center justify-center" title="Sửa">
                                        ✏️
                                    </a>

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="9" class="text-center py-12 text-slate-400">
                                Chưa có bản ghi nào
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- Pagination --}}
            @if($assignments->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-slate-50">

                <div class="text-sm text-slate-500">
                    Hiển thị <span class="font-semibold">{{ $assignments->firstItem() }}</span> đến <span class="font-semibold">{{ $assignments->lastItem() }}</span> trong tổng số <span class="font-semibold">{{ $assignments->total() }}</span> bản ghi
                </div>

                <nav class="flex items-center gap-1">
                    {{-- Previous --}}
                    @if ($assignments->onFirstPage())
                    <span class="px-3 py-2 rounded-lg text-slate-400 bg-slate-100 text-sm">← Trước</span>
                    @else
                    <a href="{{ $assignments->previousPageUrl() }}" class="px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 text-sm">← Trước</a>
                    @endif

                    {{-- Next --}}
                    @if ($assignments->hasMorePages())
                    <a href="{{ $assignments->nextPageUrl() }}" class="px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 text-sm">Tiếp →</a>
                    @else
                    <span class="px-3 py-2 rounded-lg text-slate-400 bg-slate-100 text-sm">Tiếp →</span>
                    @endif
                </nav>

            </div>
            @endif

        </div>

    </div>

</x-admin-layout>
