<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-900">Đăng ký tài khoản</h1>
        <p class="mt-1 text-sm text-gray-500">Tạo tài khoản mới để sử dụng hệ thống.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="role_id" value="Quyền tài khoản" class="text-gray-700 font-medium" />
            <select
                id="role_id"
                name="role_id"
                required
                class="block mt-1.5 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>Chọn quyền</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" {{ (string) old('role_id') === (string) $role->id ? 'selected' : '' }}>
                        {{ $role->label() }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="username" value="Tên đăng nhập" class="text-gray-700 font-medium" />
            <x-text-input
                id="username"
                class="block mt-1.5 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                type="text"
                name="username"
                :value="old('username')"
                required
                autofocus
                autocomplete="username"
                placeholder="Ví dụ: nguyenvana"
            />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="name" value="Họ và tên" class="text-gray-700 font-medium" />
            <x-text-input
                id="name"
                class="block mt-1.5 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                type="text"
                name="name"
                :value="old('name')"
                required
                autocomplete="name"
                placeholder="Nhập họ và tên đầy đủ"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" class="text-gray-700 font-medium" />
            <x-text-input
                id="email"
                class="block mt-1.5 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Mật khẩu" class="text-gray-700 font-medium" />
            <x-text-input
                id="password"
                class="block mt-1.5 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Tối thiểu 8 ký tự"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Xác nhận mật khẩu" class="text-gray-700 font-medium" />
            <x-text-input
                id="password_confirmation"
                class="block mt-1.5 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Nhập lại mật khẩu"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 mt-2 bg-gradient-to-r from-indigo-600 to-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wide hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow-md hover:shadow-lg">
            Đăng ký
        </button>

        <p class="text-center text-sm text-gray-600 pt-2">
            Đã có tài khoản?
            <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold transition">
                Đăng nhập
            </a>
        </p>
    </form>
</x-guest-layout>
