@php
    $model = $overtimeRequest ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label text-muted mb-1">Nhân viên</label>
        <div class="fw-semibold">{{ $model?->employee?->full_name ?? '—' }}</div>
    </div>
    <div class="col-md-6">
        <label class="form-label text-muted mb-1">Phòng ban</label>
        <div class="fw-semibold">{{ $model?->employee?->department?->department_name ?? '—' }}</div>
    </div>
    <div class="col-md-4">
        <label class="form-label text-muted mb-1">Ngày tăng ca</label>
        <div class="fw-semibold">{{ optional($model?->work_date)->format('d/m/Y') ?? '—' }}</div>
    </div>
    <div class="col-md-4">
        <label class="form-label text-muted mb-1">Giờ bắt đầu</label>
        <div class="fw-semibold">{{ \App\Support\TimeInput::forInput($model?->start_time) ?: '—' }}</div>
    </div>
    <div class="col-md-4">
        <label class="form-label text-muted mb-1">Giờ kết thúc</label>
        <div class="fw-semibold">{{ \App\Support\TimeInput::forInput($model?->end_time) ?: '—' }}</div>
    </div>
    <div class="col-md-4">
        <label class="form-label text-muted mb-1">Tổng giờ</label>
        <div class="fw-semibold">{{ $model?->total_hours ?? '—' }}</div>
    </div>
    <div class="col-md-4">
        <label class="form-label text-muted mb-1">Trạng thái</label>
        <div><x-status-badge :model="$model" /></div>
    </div>
    <div class="col-md-4">
        <label class="form-label text-muted mb-1">{{ $approverLabel ?? 'Người duyệt' }}</label>
        <div class="fw-semibold">
            @if(($showApprovedAt ?? false) && $model?->approved_at)
                {{ optional($model->approved_at)->format('d/m/Y H:i') }}
            @else
                {{ $model?->approver?->name ?? '—' }}
            @endif
        </div>
    </div>
    <div class="col-12">
        <label class="form-label text-muted mb-1">Lý do</label>
        <div class="border rounded-3 p-3 bg-light">{{ $model?->reason ?: '—' }}</div>
    </div>
    @if($model?->reject_reason)
        <div class="col-12">
            <label class="form-label text-muted mb-1">Lý do từ chối</label>
            <div class="border rounded-3 p-3 bg-danger-subtle text-danger-emphasis">{{ $model->reject_reason }}</div>
        </div>
    @endif
</div>
