<x-app-layout title="KPI của tôi">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">KPI của tôi</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách mục tiêu KPI ({{ $employeeKpis->total() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Mã KPI</th>
                            <th>Tên mục tiêu & Mô tả</th>
                            <th>Tiến độ</th>
                            <th>Hạn chót</th>
                            <th>Trạng thái</th>
                            <th>Người giao</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employeeKpis as $employeeKpi)
                        <tr>
                            <td>{{ $employeeKpi->kpi->code ?? 'N/A' }}</td>
                            <td>
                                <strong>{{ $employeeKpi->target }}</strong>
                                <p class="text-muted text-sm mb-0">{{ Str::limit($employeeKpi->comment, 100) }}</p>
                            </td>
                            <td>
                                <div class="progress mb-2">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $employeeKpi->progress ?? 0 }}%;"
                                        aria-valuenow="{{ $employeeKpi->progress ?? 0 }}" aria-valuemin="0"
                                        aria-valuemax="100">{{ $employeeKpi->progress ?? 0 }}%</div>
                                </div>
                            </td>
                            <td>{{ $employeeKpi->deadline->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $employeeKpi->status_color }}">{{ $employeeKpi->status_label }}</span>
                            </td>
                            <td>
                                {{ $employeeKpi->kpiAssignment->manager->name ?? 'N/A' }}
                            </td>
                            <td>
                                <a href="" class="btn btn-info btn-sm"
                                   >
                                 Cập nhật tiến độ
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">Bạn chưa được giao mục tiêu KPI nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employeeKpis->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $employeeKpis->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>