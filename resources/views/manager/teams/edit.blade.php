<x-manager-layout title="Sửa nhóm" :subtitle="$team->name">
    <div class="manager-page max-w-2xl space-y-6">
        <div>
            <a href="{{ route('manager.teams.show', $team) }}" class="text-sm text-teal-600 hover:underline">← Quay lại nhóm</a>
            <h2 class="mt-2 text-2xl font-bold text-slate-800">Sửa thông tin nhóm</h2>
        </div>

        <div class="manager-panel p-6">
            <form method="POST" action="{{ route('manager.teams.update', $team) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="manager-label">Tên nhóm <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $team->name) }}" required class="manager-field">
                </div>
                <div>
                    <label class="manager-label">Mô tả</label>
                    <textarea name="description" rows="3" class="manager-field">{{ old('description', $team->description) }}</textarea>
                </div>
                <div>
                    <label class="manager-label">Trưởng nhóm</label>
                    <select name="leader_employee_id" class="manager-field">
                        <option value="">-- Không gán --</option>
                        @foreach($leaders as $leader)
                            @if(! $assignedLeaderIds->contains($leader->id) || (int) $team->leader_employee_id === (int) $leader->id)
                                <option value="{{ $leader->id }}" @selected(old('leader_employee_id', $team->leader_employee_id) == $leader->id)>
                                    {{ $leader->full_name }} ({{ $leader->employee_code }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('leader_employee_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="manager-label">Trạng thái</label>
                    <select name="status" class="manager-field">
                        <option value="active" @selected(old('status', $team->status) === 'active')>Đang hoạt động</option>
                        <option value="inactive" @selected(old('status', $team->status) === 'inactive')>Ngưng hoạt động</option>
                    </select>
                </div>
                <div class="flex flex-wrap justify-between gap-3">
                    <button type="button" onclick="if(confirm('Xóa nhóm này? Thành viên sẽ được gỡ khỏi nhóm.')) document.getElementById('delete-team-form').submit()" class="text-sm font-semibold text-rose-600 hover:underline">Xóa nhóm</button>
                    <div class="flex gap-3">
                        <a href="{{ route('manager.teams.show', $team) }}" class="manager-btn-secondary">Hủy</a>
                        <button type="submit" class="manager-btn-primary">Lưu</button>
                    </div>
                </div>
            </form>
            <form id="delete-team-form" method="POST" action="{{ route('manager.teams.destroy', $team) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</x-manager-layout>
