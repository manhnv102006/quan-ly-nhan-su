<x-app-layout :title="'Chấm KPI cho ' . ($employeeKpi->employee->full_name ?? '')">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chấm KPI</h1>

        <div>
            <a href="{{ route('manager.kpis.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin KPI</h6>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nhân viên:</strong> {{ $employeeKpi->employee->full_name ?? 'N/A' }}</p>
                    <p><strong>Mã nhân viên:</strong> {{ $employeeKpi->employee->employee_code ?? '' }}</p>
                    <p><strong>Tên mục tiêu:</strong> {{ $employeeKpi->target }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Hạn chót:</strong> {{ optional($employeeKpi->deadline)->format('d/m/Y') ?? '—' }}</p>
                    <p><strong>Tiến độ hiện tại:</strong>
                        @php $progress = max(0, min(100, (int) ($employeeKpi->progress ?? 0))); @endphp
                        {{ $progress }}%
                    </p>
                    <p><strong>Trạng thái:</strong>
                        <span class="badge {{ $employeeKpi->status_color ?? 'badge-secondary' }}">{{ $employeeKpi->status_label ?? '—' }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Nhập điểm & nhận xét</h6>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('manager.kpis.employee_kpis.score.update', $employeeKpi) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="score" class="form-label"><strong>Điểm (0–100)</strong></label>
                    <input
                        id="score"
                        type="number"
                        name="score"
                        class="form-control @error('score') is-invalid @enderror"
                        value="{{ old('score', $employeeKpi->score) }}"
                        min="0"
                        max="100"
                        required
                    >
                    @error('score')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="comment" class="form-label"><strong>Nhận xét</strong></label>
                    <textarea
                        id="comment"
                        name="comment"
                        rows="4"
                        class="form-control @error('comment') is-invalid @enderror"
                        placeholder="Nhập nhận xét cho nhân viên..."
                    >{{ old('comment', $employeeKpi->comment) }}</textarea>
                    @error('comment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Lưu
                </button>
            </form>
        </div>
    </div>
</x-app-layout>

