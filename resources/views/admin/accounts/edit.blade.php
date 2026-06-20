<x-admin-layout title="Sửa tài khoản">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Sửa tài khoản</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Cập nhật thông tin: {{ $user->username }}
                </p>
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

            <form action="{{ route('admin.accounts.update', $user) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="role_id" class="block text-sm font-semibold text-slate-700 mb-2">
                        Vai trò <span class="text-red-500">*</span>
                    </label>
                    <select id="role_id" name="role_id" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('role_id') border-red-400 @enderror">
                        <option value="">-- Chọn vai trò --</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>
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
                           value="{{ old('username', $user->username) }}"
                           placeholder="VD: nguyenvana" maxlength="50" required autocomplete="username"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('username') border-red-400 @enderror">
                    @error('username')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
                        Họ và tên <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $user->name) }}"
                           placeholder="Nhập họ và tên đầy đủ" required autocomplete="name"
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
                           value="{{ old('email', $user->email) }}"
                           placeholder="email@example.com" required autocomplete="email"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-700">Xác thực email</p>
                            <div class="mt-2">
                                @if ($user->email_verified_at)
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        Đã xác thực
                                    </span>
                                    <p class="mt-2 text-xs text-slate-500">
                                        Thời gian: {{ $user->email_verified_at->format('d/m/Y H:i') }}
                                    </p>
                                @else
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                        Chưa xác thực
                                    </span>
                                @endif
                            </div>
                        </div>

                        <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                            <input type="hidden" name="email_verified" value="0">
                            <input type="checkbox" id="email_verified" name="email_verified" value="1"
                                   @checked((string) old('email_verified', $user->email_verified_at ? '1' : '0') === '1')
                                   class="w-5 h-5 rounded border-slate-300 text-violet-600 focus:ring-violet-500/30">
                            <span class="text-sm font-medium text-slate-700">Đánh dấu đã xác thực</span>
                        </label>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">
                        Bỏ chọn nếu muốn thu hồi trạng thái xác thực. Khi đổi email, hãy bỏ chọn trước nếu email mới chưa được xác minh.
                    </p>
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
                        Trạng thái <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('status') border-red-400 @enderror">
                        <option value="">-- Chọn trạng thái --</option>
                        <option value="active" @selected(old('status', $user->status) === 'active')>Hoạt động</option>
                        <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Không hoạt động</option>
                    </select>
                    @error('status')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 space-y-5">
                    <p class="text-sm text-slate-600">
                        Để trống mật khẩu nếu không muốn thay đổi.
                    </p>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                            Mật khẩu mới
                        </label>
                        <input type="password" id="password" name="password"
                               placeholder="Nhập mật khẩu mới" autocomplete="new-password"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('password') border-red-400 @enderror">
                        @error('password')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-2">
                            Xác nhận mật khẩu mới
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               placeholder="Nhập lại mật khẩu mới" autocomplete="new-password"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition">
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                        Lưu thay đổi
                    </button>
                    <a href="{{ route('admin.accounts') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                        Hủy
                    </a>
                </div>
            </form>
        </div>

    </div>

</x-admin-layout>
