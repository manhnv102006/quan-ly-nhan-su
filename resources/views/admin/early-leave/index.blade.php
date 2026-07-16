<x-admin-layout
    title="Đơn xin về sớm"
    subtitle="Quản lý và duyệt toàn bộ đơn xin về sớm của công ty."
>
    <div class="admin-page">
        <div class="admin-page-header">
            <div>
                <p class="admin-kicker">Về sớm</p>
                <h2 class="admin-title">Đơn xin về sớm</h2>
                <p class="admin-subtitle">
                    Duyệt đơn để nhân viên check-out sớm và vẫn tính đủ công.
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Filter tabs --}}
        <div class="flex gap-2 mb-4 flex-wrap">
            @foreach ([''=>'Tất cả', 'pending'=>'Chờ duyệt', 'approved'=>'Đã duyệt', 'rejected'=>'Từ chối'] as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
                   class="px-4 py-1.5 rounded-full text-xs font-semibold border transition
                       {{ request('status', '') == $val
                           ? 'bg-violet-600 text-white border-violet-600'
                           : 'bg-white text-slate-600 border-slate-200 hover:border-violet-300' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Nhân viên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Ngày</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Giờ về</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Lý do</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Trạng thái</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($requests as $req)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-sm text-slate-800">{{ $req->employee?->full_name }}</div>
                                    <div class="text-xs text-slate-400">{{ $req->employee?->department?->department_name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-700">
                                    {{ $req->request_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-violet-600">
                                    {{ \Carbon\Carbon::parse($req->leave_time)->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 max-w-[200px] truncate" title="{{ $req->reason }}">
                                    {{ $req->reason }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex border px-2.5 py-1 rounded-full text-xs font-bold {{ $req->statusBadgeClass() }}">
                                        {{ $req->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($req->isPending())
                                        <div class="flex items-center justify-center gap-2">
                                            <form method="POST" action="{{ route('admin.early-leave.approve', $req) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                        class="px-3 py-1.5 rounded-lg bg-emerald-500 text-white text-xs font-semibold hover:bg-emerald-600 transition">
                                                    Duyệt
                                                </button>
                                            </form>

                                            {{-- Reject modal trigger --}}
                                            <button onclick="openReject({{ $req->id }})"
                                                    class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 text-xs font-semibold hover:bg-rose-100 transition">
                                                Từ chối
                                            </button>
                                        </div>

                                        {{-- Reject modal --}}
                                        <div id="reject-modal-{{ $req->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                                            <div class="bg-white rounded-2xl p-6 shadow-xl w-full max-w-sm mx-4 text-left">
                                                <h3 class="text-base font-bold text-slate-800 mb-1">Từ chối đơn</h3>
                                                <p class="text-xs text-slate-500 mb-4">Nhân viên: <strong>{{ $req->employee?->full_name }}</strong>, ngày {{ $req->request_date->format('d/m/Y') }}</p>
                                                <form method="POST" action="{{ route('admin.early-leave.reject', $req) }}">
                                                    @csrf @method('PATCH')
                                                    <textarea name="reject_reason" rows="3" required
                                                              placeholder="Lý do từ chối..."
                                                              class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:ring-2 focus:ring-rose-500/20 outline-none resize-none mb-3"></textarea>
                                                    <div class="flex gap-2">
                                                        <button type="submit"
                                                                class="flex-1 py-2 rounded-xl bg-rose-500 text-white text-sm font-semibold hover:bg-rose-600 transition">
                                                            Xác nhận từ chối
                                                        </button>
                                                        <button type="button" onclick="closeReject({{ $req->id }})"
                                                                class="flex-1 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">
                                                            Hủy
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-14 text-slate-400 text-sm">
                                    Không có đơn xin về sớm nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        function openReject(id) {
            document.getElementById('reject-modal-' + id).classList.remove('hidden');
        }
        function closeReject(id) {
            document.getElementById('reject-modal-' + id).classList.add('hidden');
        }
    </script>
</x-admin-layout>
