@props([
    'stats',
    'completed' => null,
])

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted">Tổng đơn</div>
                <div class="h4 mb-0">{{ $stats['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted">Chờ duyệt</div>
                <div class="h4 mb-0 text-warning">{{ $stats['pending'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted">Đã duyệt</div>
                <div class="h4 mb-0 text-success">{{ $stats['approved'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted">{{ $completed !== null ? 'Hoàn tất' : 'Đã từ chối' }}</div>
                <div class="h4 mb-0 text-primary">
                    {{ $completed !== null ? ($stats['rejected'] + $completed) : $stats['rejected'] }}
                </div>
            </div>
        </div>
    </div>
</div>
