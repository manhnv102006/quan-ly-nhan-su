@php
    $showActions = $showActions ?? false;
    $detailRouteName = $detailRouteName ?? null;
    $editRouteName = $editRouteName ?? null;
    $theme = $theme ?? 'default';

    $headClass = $theme === 'accountant'
        ? 'bg-emerald-50/80 text-slate-500'
        : 'bg-slate-50 text-slate-500';
    $methodLabel = fn (?string $method) => match ($method) {
        'face' => 'Khuôn mặt',
        'manual', null => '—',
        default => 'Thủ công',
    };
@endphp

<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="{{ $headClass }}">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase">STT</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase">Ngày</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase">Buổi / Ca</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-emerald-700">Check-in</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">PT vào</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-indigo-700">Check-out</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">PT ra</th>
                <th class="px-4 py-3 text-center text-xs font-bold uppercase">Muộn</th>
                <th class="px-4 py-3 text-center text-xs font-bold uppercase">Về sớm</th>
                <th class="px-4 py-3 text-center text-xs font-bold uppercase">Thiếu CO</th>
                <th class="px-4 py-3 text-left text-xs font-bold uppercase">Trạng thái</th>
                @if ($showActions)
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase">Thao tác</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($sessionRows as $index => $row)
                @php
                    $attendance = $row['attendance'];
                    $rowBg = match ($row['status']) {
                        'late' => 'bg-amber-50/40',
                        'absent' => 'bg-rose-50/40',
                        'leave' => 'bg-sky-50/40',
                        default => '',
                    };
                    if ($row['missing_checkout']) {
                        $rowBg = 'bg-rose-50/60';
                    }
                @endphp
                <tr class="hover:bg-slate-50/80 transition {{ $rowBg }}">
                    <td class="px-4 py-3 text-slate-500">{{ $index + 1 }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="font-semibold text-slate-800">{{ $row['date']->format('d/m/Y') }}</span>
                        <span class="ml-1 text-xs text-slate-400">({{ $row['date']->locale('vi')->isoFormat('ddd') }})</span>
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800">{{ $row['session_label'] }}</p>
                        <p class="text-xs text-slate-500">{{ $row['shift_name'] }}</p>
                    </td>
                    <td class="px-4 py-3 font-mono font-semibold text-emerald-700">
                        {{ $row['check_in']?->format('H:i:s') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ $row['check_in'] ? $methodLabel($row['check_in_method']) : '—' }}
                    </td>
                    <td class="px-4 py-3 font-mono font-semibold text-indigo-700">
                        {{ $row['check_out']?->format('H:i:s') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ $row['check_out'] ? $methodLabel($row['check_out_method']) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if ($row['late_minutes'] > 0)
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
                                +{{ $row['late_minutes'] }}p
                            </span>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if ($row['early_minutes'] > 0)
                            <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700">
                                -{{ $row['early_minutes'] }}p
                            </span>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if ($row['missing_checkout'])
                            <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-700">Có</span>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @include('shared.attendance.status-badge', ['status' => $row['status']])
                    </td>
                    @if ($showActions && $detailRouteName)
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route($detailRouteName, $attendance) }}"
                                   class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">
                                    Chi tiết
                                </a>
                                @if ($editRouteName)
                                    <a href="{{ route($editRouteName, $attendance) }}"
                                       class="inline-flex items-center rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-700">
                                        Sửa
                                    </a>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $showActions ? 12 : 11 }}" class="px-5 py-14 text-center text-slate-500">
                        {{ $emptyMessage ?? 'Không có dữ liệu chấm công trong kỳ đã chọn.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($sessionRows->isNotEmpty() && isset($summary))
            <tfoot class="border-t-2 border-slate-200 bg-slate-50">
                <tr>
                    <td colspan="3" class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Tổng cộng</td>
                    <td class="px-4 py-3 text-center font-bold text-emerald-700">{{ $summary['check_ins'] ?? 0 }} lần</td>
                    <td></td>
                    <td class="px-4 py-3 text-center font-bold text-indigo-700">{{ $summary['check_outs'] ?? 0 }} lần</td>
                    <td colspan="{{ $showActions ? 6 : 5 }}" class="px-4 py-3 text-right text-xs text-slate-500">
                        @if (($summary['missing_checkouts'] ?? 0) > 0)
                            <span class="font-semibold text-rose-600">Thiếu check-out: {{ $summary['missing_checkouts'] }} buổi</span>
                            ·
                        @endif
                        Tổng giờ làm: <span class="font-bold text-violet-700">{{ $summary['total_hours'] ?? 0 }}h</span>
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
