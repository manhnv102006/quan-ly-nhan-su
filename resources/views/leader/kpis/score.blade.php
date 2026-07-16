<x-leader-layout :title="'Chấm KPI: ' . ($employeeKpi->employee?->full_name ?? '')" subtitle="Đánh giá KPI cá nhân thành viên">
    <div class="leader-page max-w-3xl space-y-6">
        <div>
            <a href="{{ route('leader.kpis.show', $employeeKpi) }}" class="text-xs font-semibold text-violet-700 hover:underline">← Chi tiết KPI</a>
            <h2 class="mt-2 text-2xl font-bold text-slate-900">Chấm điểm KPI cá nhân</h2>
            <p class="text-sm text-slate-500">{{ $employeeKpi->employee?->full_name }} · {{ $employeeKpi->target }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('leader.partials.stat-card', ['label' => 'Tiến độ', 'value' => $employeeKpi->progress.'%'])
            @include('leader.partials.stat-card', ['label' => 'Trạng thái', 'value' => $employeeKpi->status_label])
            @include('leader.partials.stat-card', ['label' => 'Hạn', 'value' => $employeeKpi->deadline?->format('d/m/Y') ?? '—'])
        </div>

        <div class="leader-card p-6">
            <form method="POST" action="{{ route('leader.kpis.score.update', $employeeKpi) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="leader-label">Điểm Leader (0–100) <span class="text-rose-500">*</span></label>
                    <input type="number" name="leader_score" min="0" max="100" required class="leader-field" value="{{ old('leader_score', $employeeKpi->leader_score !== null ? (int) $employeeKpi->leader_score : '') }}">
                    @error('leader_score')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="leader-label">Nhận xét</label>
                    <textarea name="leader_review" rows="4" class="leader-field" placeholder="Nhận xét về kết quả thực hiện KPI...">{{ old('leader_review', $employeeKpi->leader_review) }}</textarea>
                    @error('leader_review')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('leader.kpis.show', $employeeKpi) }}" class="leader-btn-secondary">Hủy</a>
                    <button type="submit" class="leader-btn-primary">Lưu điểm</button>
                </div>
            </form>
        </div>
    </div>
</x-leader-layout>
