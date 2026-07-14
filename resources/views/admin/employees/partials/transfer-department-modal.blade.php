<div id="transfer-department-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-xl w-full max-w-lg mx-4">
        <div class="px-6 py-5 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">Điều chuyển phòng ban</h3>
            <p class="text-sm text-slate-500 mt-1">{{ $employee->full_name }} · {{ $employee->employee_code }}</p>
        </div>

        <form action="{{ route('admin.employees.transfer-department', $employee) }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Phòng ban hiện tại</label>
                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-800">
                    {{ $employee->department?->department_name ?? 'Chưa gán' }}
                </div>
            </div>

            <div>
                <label for="to_department_id" class="block text-sm font-medium text-slate-700 mb-2">Phòng ban đích <span class="text-red-500">*</span></label>
                @php
                    $availableDepartments = $departments
                        ->where('id', '!=', $employee->department_id)
                        ->filter(fn ($department) => (int) ($department->employees_count ?? 0) < $department->maxEmployeesLimit());
                @endphp
                @if ($availableDepartments->isEmpty())
                    <p class="text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                        Không có phòng ban khác còn chỗ trống.
                    </p>
                @else
                    <select name="to_department_id" id="to_department_id" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                        <option value="">-- Chọn phòng ban mới --</option>
                        @foreach ($availableDepartments as $department)
                            <option value="{{ $department->id }}" @selected(old('to_department_id') == $department->id)>
                                {{ $department->department_name }}
                                ({{ $department->employees_count ?? 0 }}/{{ $department->maxEmployeesLimit() }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Chỉ hiển thị phòng ban còn chỗ theo giới hạn đã cấu hình.</p>
                @endif
                @error('to_department_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="effective_date" class="block text-sm font-medium text-slate-700 mb-2">Ngày hiệu lực <span class="text-red-500">*</span></label>
                <input type="date" name="effective_date" id="effective_date"
                       value="{{ old('effective_date', now()->format('Y-m-d')) }}" required
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                @error('effective_date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="note" class="block text-sm font-medium text-slate-700 mb-2">Ghi chú</label>
                <textarea name="note" id="note" rows="3" placeholder="Lý do điều chuyển (tuỳ chọn)"
                          class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">{{ old('note') }}</textarea>
                @error('note') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" id="close-transfer-modal"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="submit"
                        @disabled($availableDepartments->isEmpty())
                        class="flex-1 px-5 py-3 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Xác nhận điều chuyển
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('transfer-department-modal');
        const openBtn = document.getElementById('open-transfer-modal');
        const closeBtn = document.getElementById('close-transfer-modal');

        if (!modal || !openBtn) return;

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        openBtn.addEventListener('click', openModal);
        closeBtn?.addEventListener('click', closeModal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeModal();
        });

        @if (request('open') === 'transfer' || $errors->has('to_department_id') || $errors->has('effective_date') || $errors->has('note'))
            openModal();
        @endif
    })();
</script>
