<x-manager-layout
    title="Đề xuất từ trưởng nhóm"
    subtitle="Duyệt các đề xuất thêm/bớt thành viên do trưởng nhóm gửi lên."
>
    <div class="manager-page">
        <div class="manager-page-header">
            <div>
                <p class="manager-kicker">Nhân sự</p>
                <h2 class="manager-title">Đề xuất thành viên nhóm</h2>
                <p class="manager-subtitle">Chỉ áp dụng cho các trưởng nhóm thuộc phòng ban bạn quản lý.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!($managerLinked ?? true))
            <div class="manager-card border border-amber-100 bg-amber-50/90 p-5">
                <h3 class="text-base font-bold text-amber-800">Chưa liên kết hồ sơ nhân viên</h3>
                <p class="mt-2 text-sm leading-6 text-amber-700">
                    Tài khoản quản lý của bạn chưa được liên kết với hồ sơ nhân viên nên không thể tải danh sách đề xuất.
                </p>
            </div>
        @else
            <div class="grid grid-cols-3 gap-4">
                <div class="manager-card p-4">
                    <p class="text-xs font-semibold uppercase text-slate-400">Chờ duyệt</p>
                    <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
                </div>
                <div class="manager-card p-4">
                    <p class="text-xs font-semibold uppercase text-slate-400">Đã duyệt</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $stats['approved'] }}</p>
                </div>
                <div class="manager-card p-4">
                    <p class="text-xs font-semibold uppercase text-slate-400">Từ chối</p>
                    <p class="mt-1 text-2xl font-bold text-rose-600">{{ $stats['rejected'] }}</p>
                </div>
            </div>

            <div class="manager-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                                <th class="px-4 py-3">Trưởng nhóm</th>
                                <th class="px-4 py-3">Nhân viên</th>
                                <th class="px-4 py-3">Loại</th>
                                <th class="px-4 py-3">Lý do</th>
                                <th class="px-4 py-3">Trạng thái</th>
                                <th class="px-4 py-3">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($requests as $req)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $req->leader?->full_name }}</td>
                                    <td class="px-4 py-3">{{ $req->employee?->full_name }}</td>
                                    <td class="px-4 py-3">{{ $req->actionLabel() }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $req->reason ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $req->statusBadgeClass() }}">{{ $req->statusLabel() }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($req->isPending())
                                            <div class="flex flex-wrap gap-2">
                                                <form method="POST" action="{{ route('manager.team-requests.approve', $req) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="manager-btn-primary text-xs">Duyệt</button>
                                                </form>
                                                <form method="POST" action="{{ route('manager.team-requests.reject', $req) }}" onsubmit="return promptRejectReason(this)">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="decision_note" value="">
                                                    <button type="submit" class="manager-btn-secondary text-xs">Từ chối</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400">{{ $req->decision_note ?: '—' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">Chưa có đề xuất nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $requests->links() }}</div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function promptRejectReason(form) {
            const reason = prompt('Nhập lý do từ chối:');
            if (!reason) { return false; }
            form.querySelector('input[name="decision_note"]').value = reason;
            return true;
        }
    </script>
    @endpush
</x-manager-layout>
