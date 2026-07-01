<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">Lịch sử xử lý đơn tăng ca</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Người xử lý</th>
                        <th>Hành động</th>
                        <th>Thời gian</th>
                        <th>Mã đơn</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overtimeRequest->histories->sortByDesc('processed_at') as $history)
                        <tr>
                            <td>{{ $history->actor?->name ?? 'Hệ thống' }}</td>
                            <td><x-approval-action-badge :action="$history->action" /></td>
                            <td>{{ optional($history->processed_at)->format('d/m/Y H:i:s') }}</td>
                            <td>#{{ $history->overtime_request_id }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Chưa có lịch sử xử lý.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
