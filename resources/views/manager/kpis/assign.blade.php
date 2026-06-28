<x-app-layout title="Giao KPI cho nhân viên">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Giao KPI: {{ $assignment->kpi_title }}</h1>
        <a href="{{ route('manager.kpis.show', $assignment) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin KPI gốc (Manager)</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Mã KPI:</strong> {{ $assignment->kpi_code }}</p>
                    <p><strong>Mô tả:</strong> {{ $assignment->kpi->description ?? 'N/A' }}</p>
                    <p><strong>Mục tiêu của Manager:</strong> {{ number_format($assignment->target) }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Ngày bắt đầu:</strong> {{ $assignment->start_date->format('d/m/Y') }}</p>
                    <p><strong>Ngày kết thúc:</strong> {{ $assignment->end_date->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form giao KPI cho nhân viên</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('manager.kpis.store_assign', $assignment) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="employee_id"><strong>Chọn nhân viên</strong></label>
                    <select name="employee_id" id="employee_id" class="form-control" required>
                        <option value="">-- Chọn nhân viên trong phòng ban --</option>
                        @forelse($employeesInDepartment as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }} ({{ $employee->employee_code }})
                            </option>
                        @empty
                            <option disabled>Không có nhân viên nào trong phòng ban của bạn.</option>
                        @endforelse
                    </select>
                </div>

                <div class="form-group mt-3">
                    <label for="target">Tên mục tiêu</label>
                    <input type="text" name="target" id="target" class="form-control" value="{{ old('target') }}" placeholder="Ví dụ: Hoàn thành CRUD User" required>
                </div>

                <div class="form-group mt-3">
                    <label for="comment">Mô tả công việc</label>
                    <textarea name="comment" id="comment" class="form-control" rows="3" placeholder="Ví dụ: Hoàn thành CRUD User, Validate, Search.">{{ old('comment') }}</textarea>
                </div>

                <div class="form-group mt-3">
                    <label for="deadline">Hạn chót</label>
                    <input type="date" name="deadline" id="deadline" class="form-control" value="{{ old('deadline') }}" required>
                </div>

                <button type="submit" class="btn btn-primary mt-4"><i class="fas fa-paper-plane"></i> Giao mục tiêu</button>
            </form>
        </div>
    </div>
</x-app-layout>