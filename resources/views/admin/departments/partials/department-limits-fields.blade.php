@props(['department' => null])

@php
    $defaultMaxEmployees = \App\Models\Department::DEFAULT_MAX_EMPLOYEES;
    $minMaxEmployees = \App\Models\Department::MIN_MAX_EMPLOYEES;
    $maxMaxEmployees = \App\Models\Department::MAX_MAX_EMPLOYEES;
    $employeeCount = (int) ($department->employees_count ?? 0);

    $defaultMaxManagers = \App\Models\Department::DEFAULT_MAX_MANAGERS;
    $minMaxManagers = \App\Models\Department::MIN_MAX_MANAGERS;
    $maxMaxManagers = \App\Models\Department::MAX_MAX_MANAGERS;
    $managerCount = $department ? $department->managerCount() : 0;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label for="max_employees" class="block text-sm font-semibold text-slate-700 mb-2">
            Giới hạn nhân viên <span class="text-red-500">*</span>
        </label>
        <input
            type="number"
            id="max_employees"
            name="max_employees"
            value="{{ old('max_employees', $department->max_employees ?? $defaultMaxEmployees) }}"
            min="{{ $department ? max($minMaxEmployees, $employeeCount) : $minMaxEmployees }}"
            max="{{ $maxMaxEmployees }}"
            required
            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('max_employees') border-red-400 @enderror"
        >
        <p class="mt-1.5 text-xs text-slate-500">
            @if ($department)
                Hiện có {{ $employeeCount }} nhân viên · tối đa {{ $maxMaxEmployees }} người
            @else
                Tối thiểu {{ $minMaxEmployees }}, tối đa {{ $maxMaxEmployees }} người
            @endif
        </p>
        @error('max_employees')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="max_managers" class="block text-sm font-semibold text-slate-700 mb-2">
            Giới hạn quản lý <span class="text-red-500">*</span>
        </label>
        <input
            type="number"
            id="max_managers"
            name="max_managers"
            value="{{ old('max_managers', $department->max_managers ?? $defaultMaxManagers) }}"
            min="{{ $department ? max($minMaxManagers, $managerCount) : $minMaxManagers }}"
            max="{{ $maxMaxManagers }}"
            required
            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('max_managers') border-red-400 @enderror"
        >
        <p class="mt-1.5 text-xs text-slate-500">
            @if ($department)
                Hiện có {{ $managerCount }} quản lý · tối đa {{ $maxMaxManagers }} người
            @else
                Số quản lý tối đa trong phòng (tối thiểu {{ $minMaxManagers }})
            @endif
        </p>
        @error('max_managers')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
