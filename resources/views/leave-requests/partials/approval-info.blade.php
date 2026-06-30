@php
    $model = $leaveRequest ?? null;
    $variant = $variant ?? 'bootstrap';
@endphp

@if($model?->status === \App\Models\LeaveRequest::STATUS_APPROVED)
    @if($variant === 'tailwind')
        <div>
            <p class="text-sm text-slate-500">Quản lý phê duyệt</p>
            <p class="font-semibold">{{ $model->approverDisplayName() ?? '—' }}</p>
        </div>
        <div>
            <p class="text-sm text-slate-500">Thời gian duyệt</p>
            <p class="font-semibold">{{ optional($model->approved_at)->format('d/m/Y H:i') ?? '—' }}</p>
        </div>
    @else
        <div class="col-md-4">
            <div class="text-muted small">Quản lý phê duyệt</div>
            <div class="fw-semibold">{{ $model->approverDisplayName() ?? '—' }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-muted small">Thời gian duyệt</div>
            <div class="fw-semibold">{{ optional($model->approved_at)->format('d/m/Y H:i') ?? '—' }}</div>
        </div>
    @endif
@elseif($model?->status === \App\Models\LeaveRequest::STATUS_REJECTED)
    @if($variant === 'tailwind')
        <div>
            <p class="text-sm text-slate-500">Quản lý từ chối</p>
            <p class="font-semibold">{{ $model->rejecterDisplayName() ?? '—' }}</p>
        </div>
        <div>
            <p class="text-sm text-slate-500">Thời gian từ chối</p>
            <p class="font-semibold">{{ optional($model->rejected_at)->format('d/m/Y H:i') ?? '—' }}</p>
        </div>
        <div>
            <p class="text-sm text-slate-500">Lý do từ chối</p>
            <p class="font-semibold text-rose-600">{{ $model->reject_reason ?? '—' }}</p>
        </div>
    @else
        <div class="col-md-4">
            <div class="text-muted small">Quản lý từ chối</div>
            <div class="fw-semibold">{{ $model->rejecterDisplayName() ?? '—' }}</div>
        </div>
        <div class="col-md-4">
            <div class="text-muted small">Thời gian từ chối</div>
            <div class="fw-semibold">{{ optional($model->rejected_at)->format('d/m/Y H:i') ?? '—' }}</div>
        </div>
        <div class="col-md-12">
            <div class="text-muted small">Lý do từ chối</div>
            <div class="fw-semibold text-danger">{{ $model->reject_reason ?? '—' }}</div>
        </div>
    @endif
@endif
