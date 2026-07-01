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
            <form action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700">Mã nhân viên</label>
                    <input type="text" name="employee_code" value="{{ old('employee_code') }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                    @error('employee_code') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Họ và tên</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                    @error('full_name') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Giới tính</label>
                    <select name="gender" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                        <option value="male" @selected(old('gender') === 'male')>Nam</option>
                        <option value="female" @selected(old('gender') === 'female')>Nữ</option>
                        <option value="other" @selected(old('gender') === 'other')>Khác</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Ngày sinh</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                    @error('date_of_birth') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Số điện thoại</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                    @error('phone') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                    @error('email') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Địa chỉ</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Phòng ban</label>
                    <select name="department_id" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                        <option value="">-- Chọn phòng ban --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Chức vụ</label>
                    <select name="position_id" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                        <option value="">-- Chọn chức vụ --</option>
                        @foreach ($positions as $pos)
                            <option value="{{ $pos->id }}" @selected(old('position_id') == $pos->id)>{{ $pos->position_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Ngày vào làm</label>
                    <input type="date" name="hire_date" value="{{ old('hire_date') }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                    @error('hire_date') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Trạng thái</label>
                    <select name="status" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                        <option value="active" @selected(old('status', 'active') === 'active')>Hoạt động</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Tạm khóa</option>
                        <option value="resigned" @selected(old('status') === 'resigned')>Đã nghỉ việc</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Tài khoản liên kết</label>
                    <select name="user_id" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 text-sm">
                        <option value="">-- Chọn tài khoản để liên kết --</option>
                        @foreach ($users as $usr)
                            <option value="{{ $usr->id }}" @selected(old('user_id') == $usr->id)>{{ $usr->name }} ({{ $usr->email }})</option>
                        @endforeach
                    </select>
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
