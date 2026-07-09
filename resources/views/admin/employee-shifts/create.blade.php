<x-admin-layout title="Gán ca làm">

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Gán ca làm</h2>
                <p class="text-sm text-slate-500 mt-1">
                    @if ($selectedShift)
                        Gán ca <strong>{{ $selectedShift->shift_name }}</strong> cho nhân viên, phòng ban hoặc toàn công ty.
                    @else
                        Gán ca theo ngày, tháng, năm hoặc khoảng thời gian — cho nhân viên, phòng ban hoặc toàn công ty.
                    @endif
                </p>
            </div>
            <a href="{{ $selectedShift ? route('admin.shifts.index') : route('admin.employee-shifts.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
                ← {{ $selectedShift ? 'Quản lý ca làm việc' : 'Danh sách gán ca' }}
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <form method="POST"
                  action="{{ route('admin.employee-shifts.store') }}"
                  class="p-6 sm:p-8 space-y-6"
                  x-data="{ scope: @js(old('assignment_scope', 'employee')), period: @js(old('period_mode', 'month')) }">
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

                {{-- Phạm vi nhân viên --}}
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-5 space-y-4">
                    <p class="text-sm font-semibold text-slate-800">1. Phạm vi gán ca</p>

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
                        <label for="employee_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Nhân viên</label>
                        <select id="employee_id" name="employee_id" :disabled="scope !== 'employee'"
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
                        <label for="department_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Phòng ban</label>
                        <select id="department_id" name="department_id" :disabled="scope !== 'department'"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none disabled:bg-slate-100">
                            <option value="">-- Chọn phòng ban --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>
                                    {{ $department->department_name }} ({{ $department->department_code }})
                                    — {{ $department->active_employees_count }} nhân viên
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="scope === 'company'" x-cloak class="rounded-2xl border border-violet-100 bg-violet-50/60 px-4 py-3">
                        <p class="text-sm text-violet-800">
                            Gán ca cho <strong>{{ number_format($companyEmployeeCount) }}</strong> nhân viên đang hoạt động toàn công ty.
                        </p>
                    </div>
                </div>

                {{-- Thời gian gán ca --}}
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-5 space-y-4">
                    <p class="text-sm font-semibold text-slate-800">2. Thời gian gán ca</p>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                        <label class="cursor-pointer rounded-2xl border px-4 py-3 transition"
                               :class="period === 'single' ? 'border-violet-400 bg-violet-50' : 'border-slate-200 bg-white hover:border-violet-200'">
                            <input type="radio" name="period_mode" value="single" x-model="period" class="sr-only">
                            <span class="block text-sm font-semibold text-slate-800">Một ngày</span>
                            <span class="block text-xs text-slate-500 mt-0.5">Gán cho 1 ngày cụ thể</span>
                        </label>
                        <label class="cursor-pointer rounded-2xl border px-4 py-3 transition"
                               :class="period === 'month' ? 'border-violet-400 bg-violet-50' : 'border-slate-200 bg-white hover:border-violet-200'">
                            <input type="radio" name="period_mode" value="month" x-model="period" class="sr-only">
                            <span class="block text-sm font-semibold text-slate-800">Cả tháng</span>
                            <span class="block text-xs text-slate-500 mt-0.5">Tất cả ngày trong tháng</span>
                        </label>
                        <label class="cursor-pointer rounded-2xl border px-4 py-3 transition"
                               :class="period === 'year' ? 'border-violet-400 bg-violet-50' : 'border-slate-200 bg-white hover:border-violet-200'">
                            <input type="radio" name="period_mode" value="year" x-model="period" class="sr-only">
                            <span class="block text-sm font-semibold text-slate-800">Cả năm</span>
                            <span class="block text-xs text-slate-500 mt-0.5">365/366 ngày trong năm</span>
                        </label>
                        <label class="cursor-pointer rounded-2xl border px-4 py-3 transition"
                               :class="period === 'range' ? 'border-violet-400 bg-violet-50' : 'border-slate-200 bg-white hover:border-violet-200'">
                            <input type="radio" name="period_mode" value="range" x-model="period" class="sr-only">
                            <span class="block text-sm font-semibold text-slate-800">Khoảng ngày</span>
                            <span class="block text-xs text-slate-500 mt-0.5">Từ ngày — đến ngày</span>
                        </label>
                    </div>

                    <div x-show="period === 'single'" x-cloak class="space-y-2">
                        <label for="work_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Ngày làm</label>
                        <input id="work_date" type="date" name="work_date"
                               value="{{ old('work_date', now()->format('Y-m-d')) }}"
                               :disabled="period !== 'single'"
                               class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none disabled:bg-slate-100">
                    </div>

                    <div x-show="period === 'month'" x-cloak class="space-y-2">
                        <label for="work_month" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Tháng gán ca</label>
                        <input id="work_month" type="month" name="work_month"
                               value="{{ old('work_month', now()->format('Y-m')) }}"
                               :disabled="period !== 'month'"
                               class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none disabled:bg-slate-100">
                        <p class="text-xs text-slate-500">Hệ thống tự gán ca cho mọi ngày trong tháng đã chọn.</p>
                    </div>

                    <div x-show="period === 'year'" x-cloak class="space-y-2">
                        <label for="work_year" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Năm gán ca</label>
                        <select id="work_year" name="work_year" :disabled="period !== 'year'"
                                class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none disabled:bg-slate-100">
                            @for ($y = now()->year - 1; $y <= now()->year + 2; $y++)
                                <option value="{{ $y }}" @selected(old('work_year', now()->year) == $y)>{{ $y }}</option>
                            @endfor
                        </select>
                        <p class="text-xs text-slate-500">Hệ thống tự gán ca cho mọi ngày từ 01/01 đến 31/12 năm đã chọn.</p>
                    </div>

                    <div x-show="period === 'range'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
                        <div class="space-y-2">
                            <label for="start_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Từ ngày</label>
                            <input id="start_date" type="date" name="start_date"
                                   value="{{ old('start_date', now()->startOfMonth()->format('Y-m-d')) }}"
                                   :disabled="period !== 'range'"
                                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none disabled:bg-slate-100">
                        </div>
                        <div class="space-y-2">
                            <label for="end_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Đến ngày</label>
                            <input id="end_date" type="date" name="end_date"
                                   value="{{ old('end_date', now()->endOfMonth()->format('Y-m-d')) }}"
                                   :disabled="period !== 'range'"
                                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none disabled:bg-slate-100">
                        </div>
                        <p class="sm:col-span-2 text-xs text-slate-500">Tối đa 366 ngày mỗi lần gán.</p>
                    </div>
                </div>

                {{-- Ca làm --}}
                <div class="space-y-2">
                    <label for="shift_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">3. Ca làm</label>

                    @if ($selectedShift)
                        <input type="hidden" name="shift_id" value="{{ $selectedShift->id }}">
                        <div class="rounded-2xl border border-violet-200 bg-violet-50/60 px-4 py-3">
                            <p class="text-sm font-semibold text-violet-900">{{ $selectedShift->shift_name }}</p>
                            <p class="text-xs text-violet-700 mt-0.5">
                                {{ \Carbon\Carbon::parse($selectedShift->start_time)->format('H:i') }}
                                -
                                {{ \Carbon\Carbon::parse($selectedShift->end_time)->format('H:i') }}
                            </p>
                            <p class="text-xs text-violet-600 mt-2">Ca đã chọn từ danh sách quản lý ca làm việc.</p>
                        </div>
                    @else
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
                    @endif
                </div>

                <div class="rounded-2xl border border-amber-100 bg-amber-50/70 px-4 py-3">
                    <p class="text-sm text-amber-800">
                        <strong>Lưu ý:</strong> Nếu nhân viên đã có ca trong ngày đó, hệ thống sẽ <strong>cập nhật</strong> sang ca mới (không tạo trùng).
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <a href="{{ $selectedShift ? route('admin.shifts.index') : route('admin.employee-shifts.index') }}"
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
