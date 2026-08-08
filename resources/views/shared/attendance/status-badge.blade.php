@switch($status ?? '')
    @case('present')
        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Đi làm</span>
        @break
    @case('late')
        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Đi muộn</span>
        @break
    @case('leave')
        <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">Nghỉ phép</span>
        @break
    @case('absent')
        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Vắng mặt</span>
        @break
    @default
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $status ?: '—' }}</span>
@endswitch
