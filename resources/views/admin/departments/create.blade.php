<x-admin-layout title="Thêm phòng ban mới">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Thêm phòng ban mới</h2>
                <p class="text-sm text-slate-500 mt-1">Điền thông tin phòng ban bên dưới</p>
            </div>

            <a href="{{ route('admin.departments') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                ← Quay lại
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sm:p-8 max-w-3xl">

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold mb-1">Vui lòng kiểm tra lại thông tin:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.departments.store') }}" method="POST" class="space-y-5" id="department-create-form" novalidate>
                @csrf

                <div>
                    <label for="department_code" class="block text-sm font-semibold text-slate-700 mb-2">
                        Mã phòng ban <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="department_code"
                        name="department_code"
                        value="{{ old('department_code') }}"
                        placeholder="VD: HR, IT, SALE"
                        minlength="2"
                        maxlength="20"
                        pattern="[A-Za-z0-9_-]+"
                        required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('department_code') border-red-400 @enderror"
                    >
                    @error('department_code')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="department_name" class="block text-sm font-semibold text-slate-700 mb-2">
                        Tên phòng ban <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="department_name"
                        name="department_name"
                        value="{{ old('department_name') }}"
                        placeholder="Nhập tên phòng ban"
                        minlength="2"
                        maxlength="100"
                        required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('department_name') border-red-400 @enderror"
                    >
                    @error('department_name')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @include('admin.departments.partials.max-employees-field')

                <div>
                    <label for="manager_id" class="block text-sm font-semibold text-slate-700 mb-2">
                        Quản lý phòng ban
                    </label>
                    <select
                        id="manager_id"
                        name="manager_id"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('manager_id') border-red-400 @enderror"
                    >
                        <option value="">-- Chưa chỉ định --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('manager_id') == $employee->id)>
                                {{ $employee->full_name }} ({{ $employee->employee_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
                        Trạng thái <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="status"
                        name="status"
                        required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('status') border-red-400 @enderror"
                    >
                        <option value="">-- Chọn trạng thái --</option>
                        <option value="active" @selected(old('status') === 'active')>Hoạt động</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Không hoạt động</option>
                    </select>
                    @error('status')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">
                        Mô tả
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        placeholder="Mô tả chức năng phòng ban (không bắt buộc)"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition resize-y @error('description') border-red-400 @enderror"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                        + Thêm phòng ban
                    </button>
                    <a href="{{ route('admin.departments') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                        Hủy
                    </a>
                </div>
            </form>
        </div>

    </div>

    <script>
        (function () {
            const form = document.getElementById('department-create-form');
            if (!form) return;

            const minMaxEmployees = {{ \App\Models\Department::MIN_MAX_EMPLOYEES }};
            const maxMaxEmployees = {{ \App\Models\Department::MAX_MAX_EMPLOYEES }};
            const departmentCodePattern = /^[A-Z0-9_-]+$/;

            function setFieldError(input, message) {
                input.classList.add('border-red-400');
                let hint = input.parentElement.querySelector('[data-client-error]');
                if (!hint) {
                    hint = document.createElement('p');
                    hint.dataset.clientError = '1';
                    hint.className = 'mt-1.5 text-sm text-red-600';
                    input.parentElement.appendChild(hint);
                }
                hint.textContent = message;
            }

            function clearClientErrors() {
                form.querySelectorAll('[data-client-error]').forEach(el => el.remove());
                form.querySelectorAll('.border-red-400').forEach(el => el.classList.remove('border-red-400'));
            }

            form.addEventListener('submit', function (event) {
                clearClientErrors();
                let valid = true;

                const departmentCode = form.querySelector('#department_code');
                const departmentName = form.querySelector('#department_name');
                const maxEmployees = form.querySelector('#max_employees');
                const status = form.querySelector('#status');

                const codeValue = departmentCode.value.trim().toUpperCase();
                if (!codeValue) { setFieldError(departmentCode, 'Vui lòng nhập mã phòng ban.'); valid = false; }
                else if (codeValue.length < 2 || codeValue.length > 20) { setFieldError(departmentCode, 'Mã phòng ban phải từ 2 đến 20 ký tự.'); valid = false; }
                else if (!departmentCodePattern.test(codeValue)) { setFieldError(departmentCode, 'Mã phòng ban chỉ được chứa chữ in hoa, số, gạch ngang và gạch dưới.'); valid = false; }

                const nameValue = departmentName.value.trim();
                if (!nameValue) { setFieldError(departmentName, 'Vui lòng nhập tên phòng ban.'); valid = false; }
                else if (nameValue.length < 2) { setFieldError(departmentName, 'Tên phòng ban phải có ít nhất 2 ký tự.'); valid = false; }

                const maxEmployeesValue = Number(maxEmployees.value);
                if (maxEmployees.value.trim() === '') { setFieldError(maxEmployees, 'Vui lòng nhập giới hạn nhân viên.'); valid = false; }
                else if (!Number.isInteger(maxEmployeesValue) || maxEmployeesValue < minMaxEmployees || maxEmployeesValue > maxMaxEmployees) {
                    setFieldError(maxEmployees, 'Giới hạn nhân viên phải từ ' + minMaxEmployees + ' đến ' + maxMaxEmployees + '.');
                    valid = false;
                }

                if (!status.value) { setFieldError(status, 'Vui lòng chọn trạng thái.'); valid = false; }

                if (!valid) {
                    event.preventDefault();
                    form.querySelector('.border-red-400')?.focus();
                }
            });
        })();
    </script>

</x-admin-layout>
