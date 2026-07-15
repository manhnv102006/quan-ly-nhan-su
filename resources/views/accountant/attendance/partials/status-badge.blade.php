@switch($attendance->status)
    @case('present')
        <span class="accountant-badge bg-emerald-100 text-emerald-700">Đi làm</span>
        @break
    @case('late')
        <span class="accountant-badge bg-amber-100 text-amber-700">Đi muộn</span>
        @break
    @case('leave')
        <span class="accountant-badge bg-sky-100 text-sky-700">Nghỉ phép</span>
        @break
    @case('absent')
        <span class="accountant-badge bg-rose-100 text-rose-700">Vắng mặt</span>
        @break
    @default
        <span class="accountant-badge bg-slate-100 text-slate-600">{{ $attendance->status }}</span>
@endswitch
