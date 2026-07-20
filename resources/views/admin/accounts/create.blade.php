<x-admin-layout title="Thêm tài khoản mới">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Thêm tài khoản mới</h2>
                <p class="text-sm text-slate-500 mt-1">Tạo tài khoản đăng nhập mới cho hệ thống</p>
            </div>

            <a href="{{ route('admin.accounts') }}"
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

            <form action="{{ route('admin.accounts.store') }}" method="POST" class="space-y-5" id="account-create-form" novalidate>
                @csrf

                <div>
                    <label for="role_id" class="block text-sm font-semibold text-slate-700 mb-2">
                        Vai trò <span class="text-red-500">*</span>
                    </label>
                    <select id="role_id" name="role_id" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('role_id') border-red-400 @enderror">
                        <option value="">-- Chọn vai trò --</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                                {{ $role->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">
                        Tên đăng nhập <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="username" name="username"
                           value="{{ old('username') }}"
                           placeholder="VD: nguyenvana" minlength="3" maxlength="50"
                           pattern="[A-Za-z0-9_-]+" required autocomplete="username"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('username') border-red-400 @enderror">
                    <p class="mt-1.5 text-xs text-slate-500">3–50 ký tự, chỉ chữ cái, số, gạch ngang (-) và gạch dưới (_).</p>
                    @error('username')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
                        Họ và tên <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name') }}"
                           placeholder="Nhập họ và tên đầy đủ" minlength="2" maxlength="255" required autocomplete="name"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('name') border-red-400 @enderror">
                    @error('name')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="email@example.com" maxlength="255" required autocomplete="email"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
                        Trạng thái <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('status') border-red-400 @enderror">
                        <option value="">-- Chọn trạng thái --</option>
                        <option value="active" @selected(old('status', 'active') === 'active')>Hoạt động</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Không hoạt động</option>
                    </select>
                    @error('status')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                        Mật khẩu <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password" name="password"
                           placeholder="Nhập mật khẩu" minlength="8" maxlength="255" required autocomplete="new-password"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('password') border-red-400 @enderror">
                    <p class="mt-1.5 text-xs text-slate-500">Mật khẩu tối thiểu 8 ký tự.</p>
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-2">
                        Xác nhận mật khẩu <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="Nhập lại mật khẩu" minlength="8" maxlength="255" required autocomplete="new-password"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('password') border-red-400 @enderror">
                    <p id="password-match-error" class="mt-1.5 text-sm text-red-600 hidden">Xác nhận mật khẩu không khớp.</p>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                        + Thêm tài khoản
                    </button>
                    <a href="{{ route('admin.accounts') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                        Hủy
                    </a>
                </div>
            </form>
        </div>

    </div>

    <script>
        (function () {
            const form = document.getElementById('account-create-form');
            if (!form) return;

            const usernamePattern = /^[A-Za-z0-9_-]+$/;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

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
                hint.classList.remove('hidden');
            }

            function clearClientErrors() {
                form.querySelectorAll('[data-client-error]').forEach(function (el) {
                    el.remove();
                });
                form.querySelectorAll('.border-red-400').forEach(function (el) {
                    el.classList.remove('border-red-400');
                });
                const matchError = document.getElementById('password-match-error');
                if (matchError) matchError.classList.add('hidden');
            }

            form.addEventListener('submit', function (event) {
                clearClientErrors();

                let valid = true;
                const roleId = form.querySelector('#role_id');
                const username = form.querySelector('#username');
                const name = form.querySelector('#name');
                const email = form.querySelector('#email');
                const status = form.querySelector('#status');
                const password = form.querySelector('#password');
                const passwordConfirmation = form.querySelector('#password_confirmation');

                if (!roleId.value) {
                    setFieldError(roleId, 'Vui lòng chọn vai trò.');
                    valid = false;
                }

                const usernameValue = username.value.trim();
                if (!usernameValue) {
                    setFieldError(username, 'Vui lòng nhập tên đăng nhập.');
                    valid = false;
                } else if (usernameValue.length < 3 || usernameValue.length > 50) {
                    setFieldError(username, 'Tên đăng nhập phải từ 3 đến 50 ký tự.');
                    valid = false;
                } else if (!usernamePattern.test(usernameValue)) {
                    setFieldError(username, 'Tên đăng nhập chỉ được chứa chữ cái, số, gạch ngang và gạch dưới.');
                    valid = false;
                }

                const nameValue = name.value.trim();
                if (!nameValue) {
                    setFieldError(name, 'Vui lòng nhập họ và tên.');
                    valid = false;
                } else if (nameValue.length < 2) {
                    setFieldError(name, 'Họ và tên phải có ít nhất 2 ký tự.');
                    valid = false;
                }

                const emailValue = email.value.trim().toLowerCase();
                if (!emailValue) {
                    setFieldError(email, 'Vui lòng nhập email.');
                    valid = false;
                } else if (!emailPattern.test(emailValue)) {
                    setFieldError(email, 'Email không đúng định dạng.');
                    valid = false;
                }

                if (!status.value) {
                    setFieldError(status, 'Vui lòng chọn trạng thái.');
                    valid = false;
                }

                if (!password.value) {
                    setFieldError(password, 'Vui lòng nhập mật khẩu.');
                    valid = false;
                } else if (password.value.length < 8) {
                    setFieldError(password, 'Mật khẩu phải có ít nhất 8 ký tự.');
                    valid = false;
                }

                if (!passwordConfirmation.value) {
                    setFieldError(passwordConfirmation, 'Vui lòng xác nhận mật khẩu.');
                    valid = false;
                } else if (password.value !== passwordConfirmation.value) {
                    const matchError = document.getElementById('password-match-error');
                    if (matchError) matchError.classList.remove('hidden');
                    passwordConfirmation.classList.add('border-red-400');
                    valid = false;
                }

                if (!valid) {
                    event.preventDefault();
                    const firstInvalid = form.querySelector('.border-red-400');
                    if (firstInvalid && typeof firstInvalid.focus === 'function') {
                        firstInvalid.focus();
                    }
                }
            });
        })();
    </script>

</x-admin-layout>
