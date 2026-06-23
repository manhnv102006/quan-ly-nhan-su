<x-admin-layout title="Hợp đồng đã xóa">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Hợp đồng đã xóa</h2>
                <p class="text-sm text-slate-500 mt-1">Khôi phục hợp đồng đã xóa mềm.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.contracts.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">Quay lại danh sách</a>
                <a href="{{ route('admin.contracts.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">Tạo hợp đồng</a>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <form action="{{ route('admin.contracts.trashed') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tìm kiếm</label>
                        <input id="search" name="search" type="text" value="{{ request('search') }}" placeholder="Mã hợp đồng hoặc nhân viên"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none">
                    </div>
                    <div class="md:col-span-2 flex items-end justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">Tìm kiếm</button>
                    </div>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mã HĐ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Nhân viên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Loại hợp đồng</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Ngày xóa</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $contract)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600 font-semibold">{{ $contract->contract_code }}</td>
                                <td class="px-6 py-4 text-slate-800">{{ $contract->employee->full_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $contract->contractType->contract_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $contract->deleted_at?->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-center space-x-2 flex items-center justify-center">
                                    <a href="{{ route('admin.contracts.show', $contract->id) }}" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">Xem</a>
                                    <button type="button" onclick="openRestoreModal('{{ $contract->id }}', @json($contract->contract_code))" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 transition">Khôi phục</button>
                                    <button type="button" onclick="openForceDeleteModal('{{ $contract->id }}', @json($contract->contract_code))" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition">Xóa vĩnh viễn</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-slate-400">Không có hợp đồng nào trong thùng rác.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($contracts->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    </div>

    <div id="restore-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="mt-5 text-lg font-bold text-slate-800">Khôi phục hợp đồng?</h3>
            <p class="mt-2 text-sm text-slate-500">Bạn có chắc muốn khôi phục hợp đồng <span id="restore-contract-code" class="font-semibold text-slate-700"></span>?</p>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeRestoreModal()" class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">Hủy</button>
                <button type="button" onclick="confirmRestore()" class="flex-1 px-5 py-3 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition">Khôi phục</button>
            </div>
        </div>
    </div>

    <div id="force-delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-red-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 class="mt-5 text-lg font-bold text-slate-800">Xóa vĩnh viễn?</h3>
            <p class="mt-2 text-sm text-slate-500">Bạn có chắc muốn xóa vĩnh viễn hợp đồng <span id="delete-contract-code" class="font-semibold text-slate-700"></span>? Hành động này không thể hoàn tác.</p>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeForceDeleteModal()" class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">Hủy</button>
                <button type="button" onclick="confirmForceDelete()" class="flex-1 px-5 py-3 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 transition">Xóa vĩnh viễn</button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div id="success-toast" class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-emerald-200 shadow-lg rounded-2xl px-5 py-4 max-w-sm">
            <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>
            <p class="text-sm font-medium text-slate-700">{{ session('success') }}</p>
        </div>
    @endif

    <script>
        let restoreTargetId = null;
        let forceDeleteTargetId = null;

        function openRestoreModal(id, code) {
            restoreTargetId = id;
            document.getElementById('restore-contract-code').textContent = code;
            const modal = document.getElementById('restore-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeRestoreModal() {
            const modal = document.getElementById('restore-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            restoreTargetId = null;
        }

        function confirmRestore() {
            if (restoreTargetId) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.contracts.restore", ":id") }}'.replace(':id', restoreTargetId);
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function openForceDeleteModal(id, code) {
            forceDeleteTargetId = id;
            document.getElementById('delete-contract-code').textContent = code;
            const modal = document.getElementById('force-delete-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeForceDeleteModal() {
            const modal = document.getElementById('force-delete-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            forceDeleteTargetId = null;
        }

        function confirmForceDelete() {
            if (forceDeleteTargetId) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.contracts.forceDelete", ":id") }}'.replace(':id', forceDeleteTargetId);
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        }

        document.getElementById('restore-modal').addEventListener('click', function (e) {
            if (e.target === this) closeRestoreModal();
        });

        document.getElementById('force-delete-modal').addEventListener('click', function (e) {
            if (e.target === this) closeForceDeleteModal();
        });

        const successToast = document.getElementById('success-toast');
        if (successToast) {
            setTimeout(function () {
                successToast.style.transition = 'opacity 0.3s ease';
                successToast.style.opacity = '0';
                setTimeout(function () { successToast.remove(); }, 300);
            }, 4000);
        }
    </script>
</x-admin-layout>
