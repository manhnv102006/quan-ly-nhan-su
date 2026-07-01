@php
    $rows = isset($histories)
        ? $histories
        : ($leaveRequest?->histories ?? collect());

    if ($rows instanceof \Illuminate\Support\Collection) {
        $rows = $rows->sortByDesc('created_at');
    }

    $showEmployee = $showEmployee ?? false;
    $showLeaveRequestLink = $showLeaveRequestLink ?? false;
@endphp

<div class="card {{ $cardClass ?? 'mb-3' }}">
    <div class="card-header fw-semibold">{{ $title ?? 'Lịch sử phê duyệt nghỉ phép' }}</div>
    <div class="table-responsive">
        <table class="table table-sm table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>Thời gian</th>
                    @if($showEmployee)
                        <th>Nhân viên</th>
                    @endif
                    <th>Hành động</th>
                    <th>Người xử lý</th>
                    <th>Ghi chú</th>
                    @if($showLeaveRequestLink)
                        <th class="text-end">Đơn</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $history)
                    <tr>
                        <td>{{ optional($history->created_at)->format('d/m/Y H:i') }}</td>
                        @if($showEmployee)
                            <td>{{ $history->leaveRequest?->employee?->full_name ?? '—' }}</td>
                        @endif
                        <td><x-approval-action-badge :action="$history->action" /></td>
                        <td>{{ $history->actor?->name ?? '—' }}</td>
                        <td>{{ $history->note ?? '—' }}</td>
                        @if($showLeaveRequestLink)
                            <td class="text-end">
                                @if($history->leaveRequest)
                                    <a href="{{ route('manager.leave-requests.show', $history->leaveRequest) }}" class="btn btn-sm btn-outline-primary">
                                        #{{ $history->leave_request_id }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 4 + ($showEmployee ? 1 : 0) + ($showLeaveRequestLink ? 1 : 0) }}" class="text-center text-muted py-4">
                            Chưa có lịch sử phê duyệt.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
