<x-admin-layout title="Sửa hợp đồng">
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Sửa hợp đồng</h2>
                <p class="text-sm text-slate-500 mt-1">Cập nhật thông tin hợp đồng.</p>
            </div>
            <a href="{{ route('admin.contracts.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">Quay lại danh sách</a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <form action="{{ route('admin.contracts.update', $contract) }}" method="POST" enctype="multipart/form-data" class="grid gap-6">
                @csrf
                @method('PUT')

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="employee_id" class="block text-sm font-semibold text-slate-700">Nhân viên</label>
                        <select id="employee_id" name="employee_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none" required>
                            <option value="">Chọn nhân viên</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id', $contract->employee_id) == $employee->id ? 'selected' : '' }}>{{ $employee->full_name }} ({{ $employee->employee_code }})</option>
                            @endforeach
                        </select>
                        @error('employee_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="contract_type_id" class="block text-sm font-semibold text-slate-700">Loại hợp đồng</label>
                        <select id="contract_type_id" name="contract_type_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none" required>
                            <option value="">Chọn loại hợp đồng</option>
                            @foreach($contractTypes as $type)
                                <option value="{{ $type->id }}" {{ old('contract_type_id', $contract->contract_type_id) == $type->id ? 'selected' : '' }}>{{ $type->contract_name }}</option>
                            @endforeach
                        </select>
                        @error('contract_type_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="contract_code" class="block text-sm font-semibold text-slate-700">Mã hợp đồng</label>
                        <input id="contract_code" name="contract_code" type="text" value="{{ old('contract_code', $contract->contract_code) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none" required>
                        @error('contract_code') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="salary" class="block text-sm font-semibold text-slate-700">Lương</label>
                        <input id="salary" name="salary" type="number" value="{{ old('salary', $contract->salary) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none" min="0" required>
                        @error('salary') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-3">
                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-slate-700">Ngày bắt đầu</label>
                        <input id="start_date" name="start_date" type="date" value="{{ old('start_date', $contract->start_date?->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none" required>
                        @error('start_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-semibold text-slate-700">Ngày kết thúc</label>
                        <input id="end_date" name="end_date" type="date" value="{{ old('end_date', $contract->end_date?->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none">
                        @error('end_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="signed_date" class="block text-sm font-semibold text-slate-700">Ngày ký</label>
                        <input id="signed_date" name="signed_date" type="date" value="{{ old('signed_date', $contract->signed_date?->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none">
                        @error('signed_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="status" class="block text-sm font-semibold text-slate-700">Trạng thái</label>
                        <select id="status" name="status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none" required>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $contract->status) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="contract_file" class="block text-sm font-semibold text-slate-700">Tệp hợp đồng</label>
                        <input id="contract_file" name="contract_file" type="file" class="mt-2 w-full text-sm text-slate-700">
                        @if($contract->file_path)
                            <p class="mt-2 text-sm text-slate-500">Tệp hiện tại: <a href="{{ asset('storage/'.$contract->file_path) }}" target="_blank" class="text-violet-600 hover:underline">Xem</a></p>
                        @endif
                        @error('contract_file') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="note" class="block text-sm font-semibold text-slate-700">Ghi chú</label>
                    <textarea id="note" name="note" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none">{{ old('note', $contract->note) }}</textarea>
                    @error('note') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.contracts.index') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">Hủy</a>
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">Cập nhật hợp đồng</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
