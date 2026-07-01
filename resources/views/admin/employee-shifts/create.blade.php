<x-admin-layout title="Gán ca làm">

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Gán ca làm</h2>
                <p class="text-sm text-slate-500 mt-1">Gán ca cho từng nhân viên, theo phòng ban hoặc toàn công ty</p>
            </div>
            <a href="{{ route('admin.employee-shifts.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
                ← Danh sách gán ca
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <form method="POST"
                  action="{{ route('admin.employee-shifts.store') }}"
                  class="p-6 sm:p-8 space-y-6"
                  x-data="{ scope: @js(old('assignment_scope', 'employee')) }">
                @csrf

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4">
                        <p class="text-sm font-semibold text-rose-700">Vui lòng kiểm tra lại thông tin:</p>
                        <ul class="mt-2 list-disc pl-5 text-sm text-rose-600 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-5 space-y-4">
                    <p class="text-sm font-semibold text-slate-800">Phạm vi gán ca</p>

                    <div class="flex flex-wrap gap-x-6 gap-y-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="assignment_scope" value="employee" x-model="scope"
                                   class="text-violet-600 focus:ring-violet-500">
                            <span class="text-sm text-slate-700">Từng nhân viên</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="assignment_scope" value="department" x-model="scope"
                                   class="text-violet-600 focus:ring-violet-500">
                            <span class="text-sm text-slate-700">Theo phòng ban</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="assignment_scope" value="company" x-model="scope"
                                   class="text-violet-600 focus:ring-violet-500">
                            <span class="text-sm text-slate-700">Toàn công ty</span>
                        </label>
                    </div>

                    <div x-show="scope === 'employee'" x-cloak class="space-y-2">
                        <label for="employee_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Nhân viên
                        </label>
                        <select id="employee_id"
                                name="employee_id"
                                :disabled="scope !== 'employee'"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none disabled:bg-slate-100">
                            <option value="">-- Chọn nhân viên --</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                                    {{ $employee->full_name }} ({{ $employee->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="scope === 'department'" x-cloak class="space-y-2">
                        <label for="department_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Phòng ban
                        </label>
                        <select id="department_id"
                                name="department_id"
                                :disabled="scope !== 'department'"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none disabled:bg-slate-100">
                            <option value="">-- Chọn phòng ban --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>
                                    {{ $department->department_name }} ({{ $department->department_code }})
                                    — {{ $department->active_employees_count }} nhân viên
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500">Ca sẽ được gán cho tất cả nhân viên đang hoạt động trong phòng ban đã chọn.</p>
                    </div>

                    <div x-show="scope === 'company'" x-cloak class="rounded-2xl border border-violet-100 bg-violet-50/60 px-4 py-3">
                        <p class="text-sm text-violet-800">
                            Gán ca cho <strong>{{ number_format($companyEmployeeCount) }}</strong> nhân viên đang hoạt động toàn công ty.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="shift_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Ca làm</label>
                        <select id="shift_id" name="shift_id" required
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                            <option value="">-- Chọn ca --</option>
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}" @selected(old('shift_id') == $shift->id)>
                                    {{ $shift->shift_name }}
                                    ({{ $shift->start_time?->format('H:i') ?? substr((string) $shift->start_time, 0, 5) }}
                                    - {{ $shift->end_time?->format('H:i') ?? substr((string) $shift->end_time, 0, 5) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="work_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Ngày làm</label>
                        <input id="work_date"
                               type="date"
                               name="work_date"
                               value="{{ old('work_date', now()->format('Y-m-d')) }}"
                               required
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.employee-shifts.index') }}"
                       class="inline-flex items-center px-5 py-3 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                        Hủy
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">
                        Lưu gán ca
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>
