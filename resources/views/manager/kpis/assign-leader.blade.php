<x-manager-layout title="Giao KPI cho Trưởng nhóm" subtitle="Phân cấp KPI 2 bậc: Manager → Leader → Thành viên">
    <div class="manager-page max-w-3xl space-y-6">
        <div>
            <a href="{{ route('manager.kpis.show', $assignment) }}" class="text-sm text-teal-600 hover:underline">← Quay lại chi tiết KPI</a>
            <h2 class="mt-2 text-2xl font-bold text-slate-800">Giao KPI nhóm cho Trưởng nhóm</h2>
            <p class="text-sm text-slate-500">{{ $assignment->kpi_code }} — {{ $assignment->kpi_title }}</p>
        </div>

        <div class="rounded-2xl border border-teal-100 bg-teal-50 p-5 text-sm">
            <p class="font-semibold text-teal-800">Luồng KPI 2 bậc</p>
            <p class="mt-1 text-teal-700">Sau khi giao, Trưởng nhóm sẽ phân bổ KPI cá nhân cho thành viên, chấm điểm và gửi báo cáo tổng hợp lên bạn.</p>
            <p class="mt-2 font-semibold text-slate-800">Chỉ tiêu nhóm: {{ $assignment->formatted_target }}</p>
        </div>

        <div class="manager-panel p-6">
            <form method="POST" action="{{ route('manager.kpis.store_assign_leader', $assignment) }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Chọn Trưởng nhóm <span class="text-red-500">*</span></label>
                    <select name="leader_employee_id" required class="w-full rounded-xl border border-slate-300 focus:border-teal-500 focus:ring-teal-500">
                        <option value="">-- Chọn Trưởng nhóm --</option>
                        @forelse($leadersInDepartment as $leader)
                            <option value="{{ $leader->id }}" @selected(old('leader_employee_id') == $leader->id)>{{ $leader->full_name }} ({{ $leader->employee_code }})</option>
                        @empty
                            <option disabled>Không có Trưởng nhóm trong phòng ban.</option>
                        @endforelse
                    </select>
                    @error('leader_employee_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Ghi chú cho Trưởng nhóm</label>
                    <textarea name="note" rows="3" class="w-full rounded-xl border border-slate-300 focus:border-teal-500 focus:ring-teal-500" placeholder="Hướng dẫn phân bổ, ưu tiên...">{{ old('note') }}</textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('manager.kpis.show', $assignment) }}" class="manager-btn-secondary">Hủy</a>
                    <button type="submit" class="manager-btn-primary">Giao KPI nhóm</button>
                </div>
            </form>
        </div>
    </div>
</x-manager-layout>
