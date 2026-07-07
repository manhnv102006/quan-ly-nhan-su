@php
    $actionLabels = [
        'approved' => ['label' => 'Phê duyệt', 'class' => 'bg-blue-50 text-blue-700 border-blue-100'],
        'rejected' => ['label' => 'Từ chối', 'class' => 'bg-rose-50 text-rose-700 border-rose-100'],
    ];

    $rows = isset($histories)
        ? $histories
        : ($overtimeRequest?->histories ?? collect());

    if ($rows instanceof \Illuminate\Support\Collection) {
        $rows = $rows->sortByDesc(fn ($row) => $row->processed_at ?? $row->created_at);
    }

    $showEmployee = $showEmployee ?? false;
    $showOvertimeRequestLink = $showOvertimeRequestLink ?? false;
@endphp

<section class="manager-card overflow-hidden">
    <div class="border-b border-slate-100 px-6 py-5 sm:px-7">
        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-teal-600">Lịch sử</p>
        <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-800">Lịch sử phê duyệt gần đây</h3>
    </div>

    @if($rows->isEmpty())
        <div class="px-6 py-10 text-center sm:px-7">
            <p class="text-sm font-semibold text-slate-700">Chưa có lịch sử phê duyệt.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Thời gian</th>
                        @if($showEmployee)
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Nhân viên</th>
                        @endif
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Hành động</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Người xử lý</th>
                        @if($showOvertimeRequestLink)
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Đơn</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rows as $history)
                        @php
                            $action = $actionLabels[$history->action] ?? ['label' => $history->action, 'class' => 'bg-slate-100 text-slate-600 border-slate-200'];
                        @endphp
                        <tr class="transition hover:bg-slate-50/50">
                            <td class="px-6 py-4 text-xs font-medium text-slate-600">
                                {{ optional($history->processed_at ?? $history->created_at)->format('d/m/Y H:i') }}
                            </td>
                            @if($showEmployee)
                                <td class="px-6 py-4 text-sm text-slate-800">{{ $history->overtimeRequest?->employee?->full_name ?? '—' }}</td>
                            @endif
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $action['class'] }}">
                                    {{ $action['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ $history->actor?->name ?? '—' }}</td>
                            @if($showOvertimeRequestLink)
                                <td class="px-6 py-4 text-center">
                                    @if($history->overtimeRequest)
                                        <a href="{{ route('manager.overtime-requests.show', $history->overtimeRequest) }}"
                                           class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                            #{{ $history->overtime_request_id }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
