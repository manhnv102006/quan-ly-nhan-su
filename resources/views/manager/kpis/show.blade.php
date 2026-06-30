<x-app-layout :title="'Chi tiết KPI: ' . $assignment->kpi_title">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chi tiết KPI: {{ $assignment->kpi_title }}</h1>
        <div>
            <a href="{{ route('manager.kpis.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
            <a href="{{ route('manager.kpis.assign', $assignment) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Giao mục tiêu mới
            </a>
        </div>
    </div>

    {{-- Thông tin KPI gốc --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin KPI gốc (Giao cho bạn)</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Mã KPI:</strong> {{ $assignment->kpi_code }}</p>
                    <p><strong>Mô tả:</strong> {{ $assignment->kpi->description ?? 'N/A' }}</p>
                    <p><strong>Mục tiêu của bạn:</strong> {{ number_format($assignment->target) }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Người giao:</strong> {{ $assignment->assignedBy->name ?? 'N/A' }}</p>
                    <p><strong>Ngày bắt đầu:</strong> {{ $assignment->start_date->format('d/m/Y') }}</p>
                    <p><strong>Ngày kết thúc:</strong> {{ $assignment->end_date->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Danh sách mục tiêu đã giao cho nhân viên --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Các mục tiêu đã giao cho nhân viên</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Tên mục tiêu</th>
                            <th>Mô tả</th>
                            <th>Điểm</th>
                            <th>Review</th>
                            <th>Hạn chót</th>
                            <th>Tiến độ</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignment->employeeKpis as $goal)
                            <tr>
                                @php
                                    $progress = max(0, min(100, (int) ($goal->progress ?? 0)));
                                @endphp
                                <td>
                                    {{ $goal->employee->full_name ?? 'N/A' }}
                                    <p class="text-muted text-sm mb-0">{{ $goal->employee->employee_code ?? '' }}</p>
                                </td>
                                <td>{{ $goal->target }}</td>
                                <td>{{ Str::limit($goal->comment, 100) }}</td>
                                <td>{{ $goal->score !== null ? $goal->score : '—' }}</td>
                                <td>{{ Str::limit($goal->review, 100) }}</td>
                                <td>{{ $goal->deadline->format('d/m/Y') }}</td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" @style(['width: ' . $progress . '%']) aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">{{ $progress }}%</div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $goal->status_color }}">{{ $goal->status_label }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('manager.kpis.employee_kpis.score.edit', $goal) }}" class="btn btn-success btn-sm" title="Chấm KPI">
                                        <i class="fas fa-star"></i> Chấm KPI
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Chưa có mục tiêu nào được giao cho nhân viên.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

