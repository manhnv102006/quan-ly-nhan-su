<x-manager-layout title="Tạo nhóm" subtitle="Nhóm làm việc trong phòng ban">
    <div class="manager-page max-w-2xl space-y-6">
        <div>
            <a href="{{ route('manager.teams.index') }}" class="text-sm text-teal-600 hover:underline">← Danh sách nhóm</a>
            <h2 class="mt-2 text-2xl font-bold text-slate-800">Tạo nhóm mới</h2>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="manager-panel p-6">
            <form method="POST" action="{{ route('manager.teams.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="manager-label">Tên nhóm <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="manager-field" placeholder="Ví dụ: Nhóm Phát triển Backend">
                    @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="manager-label">Mô tả</label>
                    <textarea name="description" rows="3" class="manager-field" placeholder="Mục tiêu, phạm vi công việc của nhóm...">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="manager-label">Trưởng nhóm</label>
                    <select name="leader_employee_id" class="manager-field">
                        <option value="">-- Chọn sau --</option>
                        @foreach($leaders as $leader)
                            @if(! $assignedLeaderIds->contains($leader->id))
                                <option value="{{ $leader->id }}" @selected(old('leader_employee_id') == $leader->id)>
                                    {{ $leader->full_name }} ({{ $leader->employee_code }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Chỉ hiển thị nhân viên có vai trò Trưởng nhóm chưa được gán nhóm khác.</p>
                    @error('leader_employee_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('manager.teams.index') }}" class="manager-btn-secondary">Hủy</a>
                    <button type="submit" class="manager-btn-primary">Tạo nhóm</button>
                </div>
            </form>
        </div>
    </div>
</x-manager-layout>
