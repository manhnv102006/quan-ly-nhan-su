<x-admin-layout title="Thêm thông báo">

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Thêm thông báo</h2>
                <p class="text-sm text-slate-500 mt-1">Tạo và gửi thông báo tới người dùng trong hệ thống</p>
            </div>
            <a href="{{ route('notifications.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
                ← Danh sách thông báo
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <form action="{{ route('admin.notifications.store') }}" method="POST"
                  class="p-6 sm:p-8 space-y-6"
                  x-data="{ audience: @js(old('audience', 'all')) }">
                @csrf

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4">
                        <p class="text-sm font-semibold text-rose-700">Vui lòng kiểm tra lại thông tin:</p>
                        <ul class="mt-2 list-disc pl-5 text-sm text-rose-600 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Tiêu đề</label>
                        <input id="title" name="title" type="text" value="{{ old('title') }}" required
                               placeholder="VD: Bảng lương tháng 6 đã duyệt"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                    </div>

                    <div class="space-y-2">
                        <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Loại thông báo</label>
                        <select id="type" name="type" required
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                            @foreach ($typeMeta as $typeKey => $meta)
                                <option value="{{ $typeKey }}" @selected(old('type', 'system') === $typeKey)>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="content" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Nội dung</label>
                    <textarea id="content" name="content" rows="5" required
                              placeholder="Nhập nội dung chi tiết thông báo..."
                              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">{{ old('content') }}</textarea>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-5 space-y-4">
                    <p class="text-sm font-semibold text-slate-800">Đối tượng nhận</p>

                    <div class="flex flex-wrap gap-x-6 gap-y-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="audience" value="all" x-model="audience"
                                   class="text-violet-600 focus:ring-violet-500">
                            <span class="text-sm text-slate-700">Tất cả tài khoản đang hoạt động</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="audience" value="departments" x-model="audience"
                                   class="text-violet-600 focus:ring-violet-500">
                            <span class="text-sm text-slate-700">Theo phòng ban</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="audience" value="selected" x-model="audience"
                                   class="text-violet-600 focus:ring-violet-500">
                            <span class="text-sm text-slate-700">Chọn người nhận cụ thể</span>
                        </label>
                    </div>

                    <div x-show="audience === 'departments'" x-cloak class="space-y-3">
                        <p class="text-xs text-slate-500">Chọn một hoặc nhiều phòng ban — thông báo gửi tới nhân viên có tài khoản trong phòng ban đó</p>

                        <div class="max-h-72 overflow-y-auto rounded-2xl border border-slate-200 bg-white divide-y divide-slate-100">
                            @forelse ($departments as $department)
                                <label class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-violet-50/50 transition">
                                    <input type="checkbox"
                                           name="department_ids[]"
                                           value="{{ $department->id }}"
                                           @checked(in_array($department->id, array_map('intval', old('department_ids', []))))
                                           class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-slate-800">{{ $department->department_name }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $department->department_code }}
                                            · {{ $department->linked_users_count }} tài khoản liên kết
                                        </p>
                                    </div>
                                </label>
                            @empty
                                <p class="px-4 py-8 text-center text-sm text-slate-500">Không có phòng ban đang hoạt động</p>
                            @endforelse
                        </div>
                    </div>

                    <div x-show="audience === 'selected'" x-cloak class="space-y-3">
                        <p class="text-xs text-slate-500">Chọn một hoặc nhiều người dùng bên dưới</p>

                        <div class="max-h-72 overflow-y-auto rounded-2xl border border-slate-200 bg-white divide-y divide-slate-100">
                            @forelse ($users as $user)
                                <label class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-violet-50/50 transition">
                                    <input type="checkbox"
                                           name="user_ids[]"
                                           value="{{ $user->id }}"
                                           @checked(in_array($user->id, array_map('intval', old('user_ids', []))))
                                           class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-slate-800">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }} · {{ $user->role?->name ?? '—' }}</p>
                                    </div>
                                </label>
                            @empty
                                <p class="px-4 py-8 text-center text-sm text-slate-500">Không có tài khoản đang hoạt động</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <a href="{{ route('notifications.index') }}"
                       class="inline-flex items-center px-5 py-3 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                        Hủy
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">
                        Gửi thông báo
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>
