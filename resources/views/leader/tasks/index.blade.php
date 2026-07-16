<x-leader-layout title="Công việc / Task" subtitle="Task theo KPI nhóm">
    <div class="leader-page">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Công việc / Task</h2>
            <p class="text-sm text-slate-500">Danh sách task thuộc các KPI được giao cho nhóm</p>
        </div>

        <form method="GET" class="leader-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[240px] flex-1">
                <label class="leader-label">Tìm kiếm</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Tên task, KPI..." class="leader-field">
            </div>
            <button type="submit" class="leader-btn-primary">Lọc</button>
        </form>

        <div class="leader-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-sm">
                    <thead>
                        <tr class="bg-fuchsia-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Task</th>
                            <th class="px-4 py-3">KPI</th>
                            <th class="px-4 py-3">Nhân viên liên quan</th>
                            <th class="px-4 py-3">Mô tả</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tasks as $task)
                            @php
                                $assignees = ($employeeKpiMap->get($task->kpi_id) ?? collect())
                                    ->pluck('employee.full_name')
                                    ->filter()
                                    ->unique()
                                    ->implode(', ');
                            @endphp
                            <tr class="hover:bg-fuchsia-50/30">
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $task->title }}</td>
                                <td class="px-4 py-3 text-violet-700">{{ $task->kpi?->title ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $assignees ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ Str::limit($task->description ?? '—', 80) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-14 text-center text-slate-500">Chưa có task trong nhóm.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tasks->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $tasks->links() }}</div>
            @endif
        </div>
    </div>
</x-leader-layout>
