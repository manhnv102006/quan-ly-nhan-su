@props(['departments', 'selected' => null, 'currentDepartmentId' => null, 'name' => 'department_id', 'required' => true, 'placeholder' => '-- Chọn phòng ban --'])

<select name="{{ $name }}" @if($required) required @endif
        class="mt-1 w-full rounded-xl border px-4 py-3 text-slate-800 text-sm @error($name) border-rose-400 @else border-slate-200 @enderror">
    <option value="">{{ $placeholder }}</option>
    @foreach ($departments as $dept)
        @php
            $count = (int) ($dept->employees_count ?? $dept->employeeCount());
            $limit = $dept->maxEmployeesLimit();
            $isCurrent = $currentDepartmentId && (int) $currentDepartmentId === (int) $dept->id;
            $isFull = $count >= $limit && ! $isCurrent;
        @endphp
        <option value="{{ $dept->id }}"
                @selected((string) old($name, $selected) === (string) $dept->id)
                @disabled($isFull)>
            {{ $dept->department_name }} ({{ $count }}/{{ $limit }})@if($isFull) — Đã đầy @endif
        </option>
    @endforeach
</select>
<p class="mt-1 text-xs text-slate-400">Mỗi phòng ban có giới hạn nhân viên riêng. Phòng đã đầy sẽ không thể chọn.</p>
