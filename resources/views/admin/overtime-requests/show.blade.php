@php
    $canAdminDecide = $overtimeRequest->isPending();
    $isFromManager = $overtimeRequest->employee?->hasManagerRole() ?? false;
@endphp

<x-admin-layout title="Chi tiết đơn tăng ca">
    <div class="container-fluid py-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Chi tiết đơn tăng ca</h4>
                <p class="text-muted mb-0">
                    @if ($canAdminDecide)
                        @if ($isFromManager)
                            Đơn tăng ca của quản lý — Admin có thể duyệt hoặc từ chối.
                        @else
                            Đơn tăng ca đang chờ duyệt — Admin có thể duyệt hoặc từ chối.
                        @endif
                    @elseif ($isFromManager)
                        Đơn tăng ca của quản lý đã được xử lý.
                    @else
                        Đơn tăng ca đã được xử lý.
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if ($canAdminDecide)
                    <form action="{{ route('admin.overtime-requests.approve', $overtimeRequest) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success" onclick="return confirm('Duyệt đơn tăng ca của {{ $overtimeRequest->employee?->full_name }}?')">Duyệt</button>
                    </form>
                    <button type="button" class="btn btn-danger" data-bs-toggle="collapse" data-bs-target="#reject-form">Từ chối</button>
                @endif
                <a href="{{ route('admin.overtime-requests.edit', $overtimeRequest) }}" class="btn btn-warning">Sửa</a>
                <form action="{{ route('admin.overtime-requests.destroy', $overtimeRequest) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa đơn tăng ca này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Xóa</button>
                </form>
                <a href="{{ route('admin.overtime-requests.index') }}" class="btn btn-outline-secondary">Quay lại</a>
            </div>
        </div>

        <x-flash-messages />

        @if ($canAdminDecide)
            <div id="reject-form" class="collapse mb-3">
                <div class="card border-danger">
                    <div class="card-body">
                        <form action="{{ route('admin.overtime-requests.reject', $overtimeRequest) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="form-label">Lý do từ chối</label>
                            <textarea name="reject_reason" rows="3" class="form-control mb-3" required>{{ old('reject_reason') }}</textarea>
                            @error('reject_reason')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                            <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                @include('overtime-requests.partials.detail-fields', ['overtimeRequest' => $overtimeRequest])
            </div>
        </div>
    </div>
</x-admin-layout>
