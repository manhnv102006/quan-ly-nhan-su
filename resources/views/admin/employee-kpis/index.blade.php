<x-admin-layout title="Giao KPI">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">

            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    KPI đã giao cho nhân viên
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Tổng cộng {{ $employeeKpis->total() }} lượt giao
                </p>
            </div>

            <a href="{{ route('admin.employee-kpis.create') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                <span>+</span>
                Giao KPI
            </a>

        </div>

        {{-- Filter --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <form method="GET" action="{{ route('admin.employee-kpis.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tìm kiếm</label>
                    <input type="text" name="search" placeholder="Tên nhân viên, mã NV, tên KPI..."
                        value="{{ request('search') }}"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Trạng thái</label>
                    <div class="flex gap-2">
                        <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                            <option value="">Tất cả</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ bắt đầu</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Đang thực hiện</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                            <option value="not_completed" {{ request('status') == 'not_completed' ? 'selected' : '' }}>Không hoàn thành</option>
                        </select>
                        <button type="submit" class="px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition shrink-0">Lọc</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">STT</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Nhân viên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Phòng ban</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">KPI</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mục tiêu</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hạn</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employeeKpis as $key => $item)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ ($employeeKpis->currentPage() - 1) * $employeeKpis->perPage() + $key + 1 }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $item->employee?->full_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $item->employee?->department?->department_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $item->kpi?->title ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $item->target ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-center text-slate-600 text-sm">
                                    {{ $item->deadline ? $item->deadline->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-block px-3 py-1 rounded-lg text-xs font-medium
                                        @class([
                                            'bg-amber-100 text-amber-700' => $item->status === 'pending',
                                            'bg-blue-100 text-blue-700' => $item->status === 'in_progress',
                                            'bg-green-100 text-green-700' => $item->status === 'completed',
                                            'bg-red-100 text-red-700' => $item->status === 'not_completed',
                                        ])">
                                        {{ $item->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">
                                    Chưa giao KPI cho nhân viên nào
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($employeeKpis->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                    {{ $employeeKpis->links() }}
                </div>
            @endif

        </div>

    </div>

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
        <script>
            setTimeout(function () {
                const t = document.getElementById('success-toast');
                if (t) { t.style.transition = 'opacity 0.3s'; t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }
            }, 4000);
        </script>
    @endif

</x-admin-layout>
