<x-staff-layout
    title="Thêm thông báo"
    subtitle="Gửi thông báo nội bộ tới phòng ban {{ $managedDepartment->department_name }}"
    role="manager"
    :navigation="$navigation"
>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Thêm thông báo</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Gửi thông báo tới thành viên phòng ban
                    <span class="font-semibold text-violet-600">{{ $managedDepartment->department_name }}</span>
                </p>
            </div>
            <a href="{{ route('manager.notifications.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
                ← Danh sách thông báo
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-violet-200 bg-violet-50 px-5 py-4">
                <p class="text-sm font-medium text-violet-700">{{ session('success') }}</p>
            </div>
        @endif

        <div class="rounded-2xl border border-violet-100 bg-violet-50/60 px-5 py-4">
            <p class="text-sm text-violet-800">
                Thông báo sẽ chỉ hiển thị trong phòng ban <strong>{{ $managedDepartment->department_name }}</strong>
                ({{ $managedDepartment->department_code }}).
            </p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <form action="{{ route('manager.notifications.store') }}" method="POST"
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

                <div class="space-y-2">
                    <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Tiêu đề</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" required
                           placeholder="VD: Họp phòng ban tuần này"
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
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
                            <span class="text-sm text-slate-700">Toàn bộ phòng ban</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="audience" value="selected" x-model="audience"
                                   class="text-violet-600 focus:ring-violet-500">
                            <span class="text-sm text-slate-700">Chọn thành viên cụ thể</span>
                        </label>
                    </div>

                    <div x-show="audience === 'selected'" x-cloak class="space-y-3">
                        <p class="text-xs text-slate-500">Chọn một hoặc nhiều thành viên trong phòng ban</p>

                        <div class="max-h-72 overflow-y-auto rounded-2xl border border-slate-200 bg-white divide-y divide-slate-100">
                            @forelse ($members as $member)
                                <label class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-violet-50/50 transition">
                                    <input type="checkbox"
                                           name="user_ids[]"
                                           value="{{ $member->id }}"
                                           @checked(in_array($member->id, array_map('intval', old('user_ids', []))))
                                           class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-slate-800">{{ $member->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $member->email }} · {{ $member->role?->name ?? '—' }}</p>
                                    </div>
                                </label>
                            @empty
                                <p class="px-4 py-8 text-center text-sm text-slate-500">Không có thành viên nào có tài khoản trong phòng ban</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                @include('notifications.partials.schedule-fields', ['accent' => 'violet'])

                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <a href="{{ route('manager.notifications.index') }}"
                       class="inline-flex items-center px-5 py-3 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                        Hủy
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">
                        Lưu & gửi
                    </button>
                </div>
            </form>
        </div>

        @include('notifications.partials.pending-scheduled', ['pendingScheduled' => $pendingScheduled])
    </div>
</x-staff-layout>
