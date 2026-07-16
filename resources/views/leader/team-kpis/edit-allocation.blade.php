<x-leader-layout title="Sửa phân bổ KPI" :subtitle="$employeeKpi->employee?->full_name">
    <div class="leader-page max-w-3xl space-y-6">
        <div>
            <a href="{{ route('leader.team-kpis.show', $assignment) }}" class="text-xs font-semibold text-violet-700 hover:underline">← Quay lại KPI nhóm</a>
            <h2 class="mt-2 text-2xl font-bold text-slate-900">Điều chỉnh phân bổ KPI cá nhân</h2>
            <p class="text-sm text-slate-500">{{ $employeeKpi->employee?->full_name }} · {{ $assignment->kpi_title }}</p>
        </div>

        <div class="leader-card p-6">
            <form method="POST" action="{{ route('leader.team-kpis.update_allocation', [$assignment, $employeeKpi]) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="leader-label">Tên mục tiêu cá nhân <span class="text-rose-500">*</span></label>
                    <input type="text" name="target" value="{{ old('target', $employeeKpi->target) }}" class="leader-field" required>
                    @error('target')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="leader-label">Mô tả công việc</label>
                    <textarea name="comment" rows="3" class="leader-field">{{ old('comment', $employeeKpi->comment) }}</textarea>
                </div>
                <div>
                    <label class="leader-label">Hạn chót <span class="text-rose-500">*</span></label>
                    <input type="date" name="deadline" value="{{ old('deadline', $employeeKpi->deadline?->format('Y-m-d')) }}" class="leader-field" required max="{{ $assignment->end_date->format('Y-m-d') }}">
                    @error('deadline')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('leader.team-kpis.show', $assignment) }}" class="leader-btn-secondary">Hủy</a>
                    <button type="submit" class="leader-btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</x-leader-layout>
