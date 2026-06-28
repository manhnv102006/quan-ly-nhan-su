<x-admin-layout title="Chi tiết đơn tăng ca">
    <div class="container-fluid py-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Chi tiết đơn tăng ca</h4>
                <p class="text-muted mb-0">Thông tin đầy đủ yêu cầu tăng ca.</p>
            </div>
            <div class="d-flex gap-2">
                @if($overtimeRequest->isPending())
                    <a href="{{ route('admin.overtime-requests.edit', $overtimeRequest) }}" class="btn btn-warning">Sửa</a>
                @endif
                <a href="{{ route('admin.overtime-requests.index') }}" class="btn btn-outline-secondary">Quay lại</a>
            </div>
        </div>

        <x-flash-messages />

        <div class="card">
            <div class="card-body">
                @include('overtime-requests.partials.detail-fields', ['overtimeRequest' => $overtimeRequest])
            </div>
        </div>
    </div>
</x-admin-layout>
