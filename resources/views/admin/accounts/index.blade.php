<x-admin-layout title="Quản lý tài khoản">

    <div class="space-y-6">

        <div>
            <h2 class="text-2xl font-bold text-slate-800">Danh sách tài khoản</h2>
            <p class="text-sm text-slate-500 mt-1">
                Tổng cộng {{ $stats['total'] }} tài khoản đăng nhập hệ thống
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Tổng tài khoản</p>
                <h3 class="text-3xl font-bold mt-2">{{ $stats['total'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Đang hoạt động</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">{{ $stats['active'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Đã xác thực email</p>
                <h3 class="text-3xl font-bold mt-2 text-violet-600">{{ $stats['verified'] }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800">Danh sách tài khoản</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tên đăng nhập</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Họ tên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Vai trò</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Xác thực email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600">{{ $user->id }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 text-sm font-medium">
                                        {{ $user->username }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $user->email }}</td>
                                <td class="px-6 py-4">
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
                                        <span class="text-slate-400 text-sm">Chưa phân quyền</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($user->status === 'active')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Hoạt động</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Không hoạt động</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($user->email_verified_at)
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Đã xác thực</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">Chưa xác thực</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ $user->created_at?->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400">
                                    Chưa có tài khoản nào
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>

</x-admin-layout>
