<x-admin-layout title="Sửa nhân viên">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Sửa nhân viên</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $employee->full_name }}</p>
            </div>
            <a href="{{ route('admin.employees.show', $employee) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm">← Quay lại</a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <form action="{{ route('admin.employees.update', $employee) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700">Mã nhân viên</label>
                    <input type="text" name="employee_code" value="{{ old('employee_code', $employee->employee_code) }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                    @error('employee_code') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Họ và tên</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $employee->full_name) }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                    @error('full_name') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Giới tính</label>
                    <select name="gender" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                        <option value="male" @selected(old('gender', $employee->gender) === 'male')>Nam</option>
                        <option value="female" @selected(old('gender', $employee->gender) === 'female')>Nữ</option>
                        <option value="other" @selected(old('gender', $employee->gender) === 'other')>Khác</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Ngày sinh</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                    @error('date_of_birth') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Số điện thoại</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                    @error('phone') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                    @error('email') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Địa chỉ</label>
                    <input type="text" name="address" value="{{ old('address', $employee->address) }}"
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Phòng ban</label>
                    <select name="department_id" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                        <option value="">-- Chọn phòng ban --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id', $employee->department_id) == $dept->id)>{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Chức vụ</label>
                    <select name="position_id" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                        <option value="">-- Chọn chức vụ --</option>
                        @foreach ($positions as $pos)
                            <option value="{{ $pos->id }}" @selected(old('position_id', $employee->position_id) == $pos->id)>{{ $pos->position_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Ngày vào làm</label>
                    <input type="date" name="hire_date" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                    @error('hire_date') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Trạng thái</label>
                    <select name="status" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                        <option value="active" @selected(old('status', $employee->status) === 'active')>Hoạt động</option>
                        <option value="inactive" @selected(old('status', $employee->status) === 'inactive')>Tạm khóa</option>
                        <option value="resigned" @selected(old('status', $employee->status) === 'resigned')>Đã nghỉ việc</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Tài khoản liên kết</label>
                    @if ($users->isEmpty())
                        <p class="mt-1 text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                            Không còn tài khoản nào chưa liên kết. Hãy tạo tài khoản mới hoặc gỡ liên kết tài khoản khác trước.
                        </p>
                    @else
                        <select name="user_id" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                            <option value="">-- Chọn tài khoản để liên kết --</option>
                            @foreach ($users as $usr)
                                <option value="{{ $usr->id }}" @selected(old('user_id', $employee->user_id ?? null) == $usr->id)>
                                    {{ $usr->name }} ({{ $usr->email }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @error('user_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                @if ($documents->isNotEmpty())
                    <div class="md:col-span-2 rounded-3xl border border-slate-200 bg-slate-50/50 p-6">
                        <h3 class="text-base font-bold text-slate-800 mb-4">Tài liệu hiện có</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($documents as $document)
                                <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-800 truncate">{{ $document->document_name }}</p>
                                        <p class="text-sm text-slate-500 mt-0.5">{{ $document->typeLabel() }} · {{ $document->created_at?->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        @if ($document->existsOnDisk())
                                            <a href="{{ route('admin.employees.documents.download', [$employee, $document]) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-violet-100 text-violet-700 text-xs font-semibold hover:bg-violet-200 transition">
                                                Tải xuống
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">File không tồn tại</span>
                                        @endif
                                        <label class="inline-flex items-center gap-2 text-xs text-red-600 cursor-pointer font-medium">
                                            <input type="checkbox" name="remove_documents[]" value="{{ $document->id }}"
                                                   class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                            Xóa
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @include('admin.employees.partials.document-upload-fields')

                <div class="md:col-span-2 flex items-center justify-end gap-3 mt-4">
                    <a href="{{ route('admin.employees.show', $employee) }}" class="px-5 py-3 rounded-xl border border-slate-200 bg-white text-sm">Hủy</a>
                    <button type="submit" class="px-5 py-3 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">Cập nhật</button>
                </div>

            </form>
        </div>

    </div>

</x-admin-layout>
