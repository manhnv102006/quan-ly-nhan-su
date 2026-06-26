<x-admin-layout title="Gia hạn hợp đồng">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Gia hạn hợp đồng {{ $contract->contract_code }}</h4>
        <a class="btn btn-outline-secondary" href="{{ route('admin.contracts.index') }}">Quay lại</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <strong>Nhân viên:</strong> {{ $contract->employee->full_name ?? 'N/A' }} |
                <strong>Loại:</strong> {{ $contract->contractType->contract_name ?? '—' }} |
                <strong>Thời hạn cũ:</strong> {{ optional($contract->start_date)->format('d/m/Y') }} - {{ optional($contract->end_date)->format('d/m/Y') }}
            </div>
            <form method="POST" action="{{ route('admin.contracts.extend', $contract) }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Mã hợp đồng mới</label>
                    <input type="text" class="form-control" name="contract_code" value="{{ old('contract_code', $nextCode) }}">
                    @error('contract_code')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Loại hợp đồng *</label>
                    <select name="contract_type_id" class="form-select" required>
                        @foreach($contractTypes as $type)
                            <option value="{{ $type->id }}" @selected(old('contract_type_id', $contract->contract_type_id) == $type->id)>{{ $type->contract_name }}</option>
                        @endforeach
                    </select>
                    @error('contract_type_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ngày bắt đầu *</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($contract->end_date)->format('Y-m-d')) }}" required>
                    @error('start_date')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ngày kết thúc *</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                    @error('end_date')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lương cơ bản *</label>
                    <input type="number" name="salary" class="form-control" min="1" value="{{ old('salary', $contract->salary) }}" required>
                    @error('salary')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phụ cấp</label>
                    <input type="number" name="allowance" class="form-control" min="0" value="{{ old('allowance', $contract->allowance) }}">
                    @error('allowance')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">File hợp đồng mới (tùy chọn)</label>
                    <input type="file" name="contract_file" class="form-control">
                    @error('contract_file')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                    @error('note')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <a class="btn btn-outline-secondary" href="{{ route('admin.contracts.index') }}">Hủy</a>
                    <button type="submit" class="btn btn-success">Lưu hợp đồng mới</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
