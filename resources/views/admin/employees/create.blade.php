<x-admin-layout title="Thêm nhân viên mới">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Thêm nhân viên</h2>
                <p class="text-sm text-slate-500 mt-1">Tạo hồ sơ nhân viên mới</p>
            </div>
            <a href="{{ route('admin.employees') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm">← Quay lại</a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4">
                    <p class="text-sm font-semibold text-rose-800">Vui lòng kiểm tra lại các trường sau:</p>
                    <ul class="mt-2 list-disc space-y-1 ps-5 text-sm text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700">Mã nhân viên <span class="text-rose-500">*</span></label>
                    <input type="text" name="employee_code" value="{{ old('employee_code') }}" required
                           maxlength="20" pattern="[A-Za-z0-9_-]+" placeholder="VD: EMP001"
                           class="mt-1 w-full rounded-xl border px-4 py-3 text-slate-800 text-sm @error('employee_code') border-rose-400 @else border-slate-200 @enderror">
                    @error('employee_code') <span class="mt-1 block text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Họ và tên <span class="text-rose-500">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                           minlength="2" maxlength="100"
                           class="mt-1 w-full rounded-xl border px-4 py-3 text-slate-800 text-sm @error('full_name') border-rose-400 @else border-slate-200 @enderror">
                    @error('full_name') <span class="mt-1 block text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Giới tính <span class="text-rose-500">*</span></label>
                    <select name="gender" required class="mt-1 w-full rounded-xl border px-4 py-3 text-slate-800 text-sm @error('gender') border-rose-400 @else border-slate-200 @enderror">
                        <option value="male" @selected(old('gender') === 'male')>Nam</option>
                        <option value="female" @selected(old('gender') === 'female')>Nữ</option>
                        <option value="other" @selected(old('gender') === 'other')>Khác</option>
                    </select>
                    @error('gender') <span class="mt-1 block text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Ngày sinh <span class="text-rose-500">*</span></label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                           max="{{ now()->subYears(16)->format('Y-m-d') }}"
                           class="mt-1 w-full rounded-xl border px-4 py-3 text-slate-800 text-sm @error('date_of_birth') border-rose-400 @else border-slate-200 @enderror">
                    @error('date_of_birth') <span class="mt-1 block text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Số điện thoại <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           maxlength="11" pattern="0[0-9]{9,10}" placeholder="VD: 0912345678"
                           class="mt-1 w-full rounded-xl border px-4 py-3 text-slate-800 text-sm @error('phone') border-rose-400 @else border-slate-200 @enderror">
                    @error('phone') <span class="mt-1 block text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           maxlength="100"
                           class="mt-1 w-full rounded-xl border px-4 py-3 text-slate-800 text-sm @error('email') border-rose-400 @else border-slate-200 @enderror">
                    @error('email') <span class="mt-1 block text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Địa chỉ</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           maxlength="255"
                           class="mt-1 w-full rounded-xl border px-4 py-3 text-slate-800 text-sm @error('address') border-rose-400 @else border-slate-200 @enderror">
                    @error('address') <span class="mt-1 block text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Phòng ban <span class="text-rose-500">*</span></label>
                    @include('admin.partials.department-select', [
                        'departments' => $departments,
                        'selected' => old('department_id'),
                    ])
                    @error('department_id') <span class="mt-1 block text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Chức vụ <span class="text-rose-500">*</span></label>
                    <select name="position_id" required class="mt-1 w-full rounded-xl border px-4 py-3 text-slate-800 text-sm @error('position_id') border-rose-400 @else border-slate-200 @enderror">
                        <option value="">-- Chọn chức vụ --</option>
                        @foreach ($positions as $pos)
                            <option value="{{ $pos->id }}" @selected(old('position_id') == $pos->id)>{{ $pos->position_name }}</option>
                        @endforeach
                    </select>
                    @error('position_id') <span class="mt-1 block text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Ngày vào làm <span class="text-rose-500">*</span></label>
                    <input type="date" name="hire_date" value="{{ old('hire_date') }}" required
                           class="mt-1 w-full rounded-xl border px-4 py-3 text-slate-800 text-sm @error('hire_date') border-rose-400 @else border-slate-200 @enderror">
                    @error('hire_date') <span class="mt-1 block text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Trạng thái <span class="text-rose-500">*</span></label>
                    <select name="status" required class="mt-1 w-full rounded-xl border px-4 py-3 text-slate-800 text-sm @error('status') border-rose-400 @else border-slate-200 @enderror">
                        <option value="active" @selected(old('status', 'active') === 'active')>Hoạt động</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Tạm khóa</option>
                        <option value="resigned" @selected(old('status') === 'resigned')>Đã nghỉ việc</option>
                    </select>
                    @error('status') <span class="mt-1 block text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Tài khoản liên kết</label>

                    <select name="user_id" class="mt-1 w-full rounded-xl border px-4 py-3 text-slate-800 text-sm @error('user_id') border-rose-400 @else border-slate-200 @enderror">
                        <option value="">-- Chọn tài khoản để liên kết --</option>
                        @foreach ($users as $usr)
                            <option value="{{ $usr->id }}" @selected(old('user_id') == $usr->id)>{{ $usr->name }} ({{ $usr->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id') <span class="mt-1 block text-red-600 text-xs">{{ $message }}</span> @enderror

                    @if ($users->isEmpty())
                        <p class="mt-1 text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                            Không còn tài khoản nào chưa liên kết. Hãy tạo tài khoản mới hoặc gỡ liên kết tài khoản khác trước.
                        </p>
                    @else
                        <select name="user_id" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                            <option value="">-- Chọn tài khoản để liên kết --</option>
                            @foreach ($users as $usr)
                                <option value="{{ $usr->id }}" @selected(old('user_id') == $usr->id)>{{ $usr->name }} ({{ $usr->email }})</option>
                            @endforeach
                        </select>
                    @endif
                    @error('user_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror

                </div>

                @include('admin.employees.partials.document-upload-fields')

                <div class="md:col-span-2 flex items-center justify-end gap-3 mt-4">
                    <a href="{{ route('admin.employees') }}" class="px-5 py-3 rounded-xl border border-slate-200 bg-white text-sm">Hủy</a>
                    <button type="submit" class="px-5 py-3 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">Lưu</button>
                </div>

            </form>
        </div>

    </div>

</x-admin-layout>
