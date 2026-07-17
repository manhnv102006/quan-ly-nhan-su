<x-manager-layout :title="$team->name" subtitle="Quản lý thành viên nhóm">
    <div class="manager-page space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('manager.teams.index') }}" class="text-sm text-teal-600 hover:underline">← Danh sách nhóm</a>
                <h2 class="mt-2 text-2xl font-bold text-slate-800">{{ $team->name }}</h2>
                <p class="text-sm text-slate-500">
                    Trưởng nhóm: <span class="font-semibold text-teal-700">{{ $team->leader?->full_name ?? 'Chưa gán' }}</span>
                    · {{ $members->count() }} thành viên
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('manager.teams.edit', $team) }}" class="manager-btn-secondary">Sửa nhóm</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @if($team->description)
            <div class="manager-card p-4 text-sm text-slate-600">{{ $team->description }}</div>
        @endif

        @if(! $team->leader_employee_id)
            <div class="manager-card border border-amber-100 bg-amber-50 p-4 text-sm text-amber-800">
                Nhóm chưa có Trưởng nhóm. <a href="{{ route('manager.teams.edit', $team) }}" class="font-semibold underline">Gán Trưởng nhóm</a> trước khi thêm thành viên.
            </div>
        @else
            <div class="manager-panel p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-slate-800">Thêm thành viên vào nhóm</h3>
                        <p class="mt-1 text-xs text-slate-500">Chọn nhân viên phòng ban chưa thuộc nhóm nào khác</p>
                    </div>
                    <a href="{{ route('manager.employees.index') }}" class="text-xs font-semibold text-teal-600 hover:underline">Xem tất cả NV phòng ban →</a>
                </div>

                @if($candidates->isEmpty())
                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                        <p class="font-medium text-slate-700">Không còn nhân viên khả dụng</p>
                        <p class="mt-1">Các nhân viên khác có thể đã thuộc nhóm khác hoặc chưa ở trạng thái đang làm việc.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('manager.teams.assign-members', $team) }}" class="mt-4 space-y-4">
                        @csrf
                        <div class="max-h-64 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3">
                            @foreach($candidates as $employee)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2.5 hover:bg-slate-50">
                                    <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                                    <span class="min-w-0 flex-1">
                                        <span class="font-medium text-slate-800">{{ $employee->full_name }}</span>
                                        <span class="text-xs text-slate-500"> · {{ $employee->employee_code }} · {{ $employee->position?->position_name ?? '—' }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('employee_ids')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                        <div class="flex flex-wrap items-center gap-3">
                            <button type="submit" class="manager-btn-primary">Gán vào nhóm</button>
                            <p class="text-xs text-slate-500">{{ $candidates->count() }} nhân viên có thể thêm</p>
                        </div>
                    </form>
                @endif
            </div>
        @endif

        <div class="manager-panel">
            <div class="manager-panel-header">
                <h3 class="text-lg font-bold text-slate-800">Thành viên nhóm ({{ $members->count() }})</h3>
            </div>
            <div class="manager-table-wrap overflow-x-auto">
                <table class="manager-table">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Chức vụ</th>
                            <th>Liên hệ</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                            <tr>
                                <td>
                                    <p class="font-bold text-slate-800">{{ $member->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $member->employee_code }}</p>
                                </td>
                                <td>{{ $member->position?->position_name ?? '—' }}</td>
                                <td>
                                    <p class="text-sm">{{ $member->email }}</p>
                                    <p class="text-xs text-slate-500">{{ $member->phone }}</p>
                                </td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('manager.teams.remove-member', [$team, $member]) }}" onsubmit="return confirm('Gỡ {{ $member->full_name }} khỏi nhóm?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Gỡ khỏi nhóm</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center text-slate-400">Chưa có thành viên trong nhóm.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-manager-layout>
