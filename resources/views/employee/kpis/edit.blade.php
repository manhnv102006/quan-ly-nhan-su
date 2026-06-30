<x-app-layout :title="'Cập nhật tiến độ: ' . $employeeKpi->target">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Cập nhật tiến độ KPI</h1>
        <a href="{{ route('employee.kpis.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form cập nhật</h6>
                </div>
                <div class="card-body">
                    @if ($employeeKpi->status === \App\Models\EmployeeKPI::STATUS_NOT_COMPLETED)
                    <div class="alert alert-danger">
                        KPI này đã quá hạn và được chuyển sang trạng thái không hoàn thành.
                    </div>
                    @endif

                    <form action="{{ route('employee.kpis.update', $employeeKpi) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="progress"><strong>Tiến độ (%)</strong></label>
                            <input type="number" name="progress" id="progress"
                                class="form-control @error('progress') is-invalid @enderror"
                                value="{{ old('progress', $employeeKpi->progress) }}"
                                min="0" max="100" step="1"
                                oninput="this.value = Math.max(0, Math.min(100, Number(this.value || 0)))"
                                @disabled($employeeKpi->status === \App\Models\EmployeeKPI::STATUS_NOT_COMPLETED)
                            required>
                            @error('progress')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mt-3">
                            <label for="status"><strong>Trạng thái</strong></label>

                            <select
                                name="status"
                                id="status"
                                class="form-control @error('status') is-invalid @enderror"
                                @disabled($employeeKpi->status === \App\Models\EmployeeKPI::STATUS_NOT_COMPLETED)
                                required
                                >
                                @if ($employeeKpi->status === \App\Models\EmployeeKPI::STATUS_NOT_COMPLETED)
                                <option selected>Không hoàn thành</option>
                                @endif

                                @foreach($statusOptions as $value => $label)

                                @php
                                $disabled = false;

                                // Đang thực hiện -> không được quay về Chờ bắt đầu
                                if (
                                $employeeKpi->status === \App\Models\EmployeeKPI::STATUS_IN_PROGRESS &&
                                $value === \App\Models\EmployeeKPI::STATUS_PENDING
                                ) {
                                $disabled = true;
                                }

                                // Hoàn thành -> không được quay về Chờ bắt đầu hoặc Đang thực hiện
                                if (
                                $employeeKpi->status === \App\Models\EmployeeKPI::STATUS_COMPLETED &&
                                in_array($value, [
                                \App\Models\EmployeeKPI::STATUS_PENDING,
                                \App\Models\EmployeeKPI::STATUS_IN_PROGRESS,
                                ])
                                ) {
                                $disabled = true;
                                }
                                @endphp

                                <option
                                    value="{{ $value }}"
                                    {{ old('status', $employeeKpi->status) == $value ? 'selected' : '' }}
                                    @disabled($disabled)>
                                    {{ $label }}
                                </option>

                                @endforeach
                            </select>

                            @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" @disabled($employeeKpi->status === \App\Models\EmployeeKPI::STATUS_NOT_COMPLETED)>
                                <i class="fas fa-save"></i> Lưu tiến độ
                            </button>
                            <a href="{{ route('employee.kpis.index') }}" class="btn btn-light">
                                Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin mục tiêu</h6>
                </div>
                <div class="card-body">
                    <p>
                        <strong>Tên mục tiêu:</strong><br>
                        {{ $employeeKpi->target }}
                    </p>
                    <p>
                        <strong>Mã KPI:</strong><br>
                        {{ $employeeKpi->kpi->code ?? 'N/A' }}
                    </p>
                    <p>
                        <strong>Tên KPI gốc:</strong><br>
                        {{ $employeeKpi->kpi->title ?? 'N/A' }}
                    </p>
                    <hr>
                    <p>
                        <strong>Người giao:</strong><br>
                        {{ $employeeKpi->kpiAssignment->manager->name ?? 'N/A' }}
                    </p>
                    <p>
                        <strong>Hạn chót:</strong><br>
                        {{ $employeeKpi->deadline->format('d/m/Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>