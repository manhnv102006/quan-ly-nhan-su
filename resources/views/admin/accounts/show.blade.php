<x-admin-layout title="Chi tiết tài khoản">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Chi tiết tài khoản</h2>
                <p class="text-slate-500 mt-1">Xem thông tin chi tiết của tài khoản</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.accounts.edit', $user) }}"
                   class="px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                    Sửa tài khoản
                </a>

                <a href="{{ route('admin.accounts') }}"
                   class="px-5 py-3 rounded-xl bg-slate-200 text-slate-700 font-medium hover:bg-slate-300 transition">
                    ← Quay lại
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-800">Thông tin tài khoản</h3>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Tên đăng nhập</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-semibold text-slate-800">
                                    {{ $user->username }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Họ và tên</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-semibold text-slate-800">
                                    {{ $user->name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Email</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">
                                    {{ $user->email }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Vai trò</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    @if ($user->role)
                                        @php
                                            $roleClass = match ($user->role->name) {
                                                'admin' => 'bg-violet-100 text-violet-700',
                                                'manager' => 'bg-blue-100 text-blue-700',
                                                'employee' => 'bg-cyan-100 text-cyan-700',
                                                default => 'bg-slate-100 text-slate-600',
                                            };
                                        @endphp
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $roleClass }}">
                                            {{ $user->role->label() }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">Chưa phân quyền</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Trạng thái</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    @if ($user->status === 'active')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Hoạt động</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-600">Không hoạt động</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Xác thực email</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    @if ($user->email_verified_at)
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Đã xác thực</span>
                                        <p class="text-xs text-slate-500 mt-2">{{ $user->email_verified_at->format('d/m/Y H:i') }}</p>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">Chưa xác thực</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Ngày tạo</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $user->created_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Cập nhật lần cuối</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $user->updated_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 text-center">
                    <div class="w-24 h-24 mx-auto rounded-3xl bg-violet-100 flex items-center justify-center">
                        <svg class="w-12 h-12 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-xl font-bold text-slate-800">{{ $user->name }}</h3>
                    <p class="text-slate-500 mt-2">{{ '@'.$user->username }}</p>

                    <div class="mt-4">
                        @if ($user->status === 'active')
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold">Hoạt động</span>
                        @else
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-slate-200 text-slate-600 text-sm font-semibold">Không hoạt động</span>
                        @endif
                    </div>

                    @if ($user->role)
                        <p class="mt-4 text-sm text-slate-500">
                            Vai trò: <span class="font-semibold text-slate-800">{{ $user->role->label() }}</span>
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800">Hồ sơ nhân viên liên kết</h3>
            </div>

            <div class="p-6">
                @if ($user->employee)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Mã nhân viên</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-medium text-slate-800">
                                {{ $user->employee->employee_code }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Phòng ban</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                {{ $user->employee->department?->department_name ?? '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Chức vụ</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                {{ $user->employee->position?->position_name ?? '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Số điện thoại</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                {{ $user->employee->phone }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Ngày vào làm</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                {{ $user->employee->hire_date ? \Illuminate\Support\Carbon::parse($user->employee->hire_date)->format('d/m/Y') : '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Trạng thái nhân viên</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                @if ($user->employee->status === 'active')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Đang làm</span>
                                @elseif ($user->employee->status === 'resigned')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Đã nghỉ</span>
                                @else
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Không hoạt động</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="py-12 text-center">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <p class="mt-4 text-slate-500 font-medium">Chưa liên kết hồ sơ nhân viên</p>
                        <p class="mt-1 text-sm text-slate-400">Tài khoản này chưa được gán với nhân viên trong hệ thống</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

</x-admin-layout>
