@props(['department' => null])

@php
    $defaultMax = \App\Models\Department::DEFAULT_MAX_EMPLOYEES;
    $minMax = \App\Models\Department::MIN_MAX_EMPLOYEES;
    $maxMax = \App\Models\Department::MAX_MAX_EMPLOYEES;
    $currentCount = (int) ($department->employees_count ?? 0);
@endphp

<div>
    <label for="max_employees" class="block text-sm font-semibold text-slate-700 mb-2">
        Giới hạn nhân viên <span class="text-red-500">*</span>
    </label>
    <input
        type="number"
        id="max_employees"
        name="max_employees"
        value="{{ old('max_employees', $department->max_employees ?? $defaultMax) }}"
        min="{{ $department ? max($minMax, $currentCount) : $minMax }}"
        max="{{ $maxMax }}"
        required
        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('max_employees') border-red-400 @enderror"
    >
    <p class="mt-1.5 text-xs text-slate-500">
        Số nhân viên tối đa thuộc phòng ban này ({{ $minMax }}–{{ $maxMax }} người).
        @if($department && $currentCount > 0)
            Hiện có <strong>{{ $currentCount }}</strong> nhân viên — không thể đặt giới hạn thấp hơn.
        @endif
    </p>
    @error('max_employees')
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
