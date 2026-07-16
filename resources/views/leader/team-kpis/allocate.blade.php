<x-leader-layout title="Phân bổ KPI cá nhân" :subtitle="$assignment->kpi_title">
    <div class="leader-page max-w-3xl space-y-6">
        <div>
            <a href="{{ route('leader.team-kpis.show', $assignment) }}" class="text-xs font-semibold text-violet-700 hover:underline">← Quay lại KPI nhóm</a>
            <h2 class="mt-2 text-2xl font-bold text-slate-900">Phân bổ KPI cá nhân</h2>
            <p class="text-sm text-slate-500">KPI nhóm: {{ $assignment->kpi_title }} · Hạn KPI: {{ $assignment->end_date->format('d/m/Y') }}</p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="leader-card p-6">
            <form method="POST" action="{{ route('leader.team-kpis.store_allocate', $assignment) }}" class="space-y-5">
                @csrf
                <div>
                    <label class="leader-label">Thành viên <span class="text-rose-500">*</span></label>
                    <select name="employee_id" class="leader-field" required>
                        <option value="">-- Chọn thành viên nhóm --</option>
                        @foreach($teamMembers as $member)
                            <option value="{{ $member->id }}" @selected(old('employee_id') == $member->id)>{{ $member->full_name }} ({{ $member->employee_code }})</option>
                        @endforeach
                    </select>
                    @error('employee_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="leader-label">Tên mục tiêu cá nhân <span class="text-rose-500">*</span></label>
                    <input type="text" name="target" value="{{ old('target') }}" class="leader-field" required placeholder="Ví dụ: Hoàn thành 20 đơn hàng">
                    @error('target')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="leader-label">Mô tả công việc</label>
                    <textarea name="comment" rows="3" class="leader-field" placeholder="Chi tiết công việc cần hoàn thành">{{ old('comment') }}</textarea>
                </div>
                <div>
                    <label class="leader-label">Hạn chót <span class="text-rose-500">*</span></label>
                    <input type="date" name="deadline" value="{{ old('deadline') }}" class="leader-field" required min="{{ now()->format('Y-m-d') }}" max="{{ $assignment->end_date->format('Y-m-d') }}">
                    @error('deadline')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('leader.team-kpis.show', $assignment) }}" class="leader-btn-secondary">Hủy</a>
                    <button type="submit" class="leader-btn-primary">Phân bổ</button>
                </div>
            </form>
        </div>
    </div>
</x-leader-layout>
