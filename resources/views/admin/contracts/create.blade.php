<x-admin-layout title="Tạo hợp đồng mới">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Tạo hợp đồng</h4>
        <a class="btn btn-outline-secondary" href="{{ route('admin.contracts.index') }}">Quay lại</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.contracts.store') }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Nhân viên *</label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">-- Chọn nhân viên --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Loại hợp đồng *</label>
                    <select name="contract_type_id" class="form-select" required>
                        <option value="">-- Chọn loại --</option>
                        @foreach($contractTypes as $type)
                            <option value="{{ $type->id }}" @selected(old('contract_type_id') == $type->id)>{{ $type->contract_name }}</option>
                        @endforeach
                    </select>
                    @error('contract_type_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mã hợp đồng</label>
                    <input type="text" name="contract_code" class="form-control" value="{{ old('contract_code', $nextCode) }}" placeholder="Để trống sẽ tự sinh">
                    @error('contract_code')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Phòng ban *</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">-- Chọn phòng ban --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Chức vụ *</label>
                    <select name="position_id" class="form-select" required>
                        <option value="">-- Chọn chức vụ --</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" @selected(old('position_id') == $pos->id)>{{ $pos->position_name }}</option>
                        @endforeach
                    </select>
                    @error('position_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lương cơ bản *</label>
                    <input type="number" name="salary" class="form-control" min="1" value="{{ old('salary') }}" required>
                    @error('salary')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Phụ cấp</label>
                    <input type="number" name="allowance" class="form-control" min="0" value="{{ old('allowance', 0) }}">
                    @error('allowance')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ngày bắt đầu *</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                    @error('start_date')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ngày kết thúc *</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                    @error('end_date')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ngày ký</label>
                    <input type="date" name="signed_date" class="form-control" value="{{ old('signed_date') }}">
                    @error('signed_date')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">File hợp đồng (pdf/doc/docx, ≤10MB)</label>
                    <input type="file" name="contract_file" class="form-control">
                    @error('contract_file')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Mô tả ngắn">{{ old('description') }}</textarea>
                    @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú nội bộ">{{ old('note') }}</textarea>
                    @error('note')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <a class="btn btn-outline-secondary" href="{{ route('admin.contracts.index') }}">Hủy</a>
                    <button type="submit" class="btn btn-primary">Lưu hợp đồng</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
