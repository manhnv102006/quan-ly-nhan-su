@props(['department' => null, 'employee' => null])

<nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
    <a href="{{ route('admin.contracts.index') }}" class="font-medium text-violet-600 hover:text-violet-700">
        Phòng ban
    </a>
    @if($department)
        <span aria-hidden="true">/</span>
        @if($employee)
            <a href="{{ route('admin.contracts.by-department', $department) }}"
               class="font-medium text-violet-600 hover:text-violet-700">
                {{ $department->department_name }}
            </a>
        @else
            <span class="font-semibold text-slate-700">{{ $department->department_name }}</span>
        @endif
    @endif
    @if($employee)
        <span aria-hidden="true">/</span>
        <span class="font-semibold text-slate-700">{{ $employee->full_name }}</span>
    @endif
</nav>
