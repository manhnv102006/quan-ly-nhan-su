<x-admin-layout title="Quản lý hợp đồng">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Danh sách hợp đồng</h2>
                <p class="text-sm text-slate-500 mt-1">Quản lý hợp đồng, lọc theo trạng thái, loại hợp đồng và nhân viên.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.contracts.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">+ Tạo hợp đồng</a>
                <a href="{{ route('admin.contracts.trashed') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">Hợp đồng đã xóa</a>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <form action="{{ route('admin.contracts.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tìm kiếm</label>
                        <input id="search" name="search" type="text" value="{{ request('search') }}" placeholder="Mã hợp đồng hoặc nhân viên"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none">
                    </div>
                    <div>
                        <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Trạng thái</label>
                        <select id="status" name="status" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none">
                            <option value="">Tất cả</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="contract_type_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Loại hợp đồng</label>
                        <select id="contract_type_id" name="contract_type_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none">
                            <option value="">Tất cả</option>
                            @foreach($contractTypes as $type)
                                <option value="{{ $type->id }}" {{ request('contract_type_id') == $type->id ? 'selected' : '' }}>{{ $type->contract_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="employee_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Nhân viên</label>
                        <select id="employee_id" name="employee_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none">
                            <option value="">Tất cả</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->full_name }} ({{ $employee->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4 text-right">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">Lọc</button>
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
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Lương</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Thời hạn</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $contract)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600 font-semibold">{{ $contract->contract_code }}</td>
                                <td class="px-6 py-4 text-slate-800">{{ $contract->employee->full_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $contract->contractType->contract_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-700">{{ number_format($contract->salary, 0, ',', '.') }} ₫</td>
                                <td class="px-6 py-4 text-slate-500">{{ optional($contract->start_date)->format('d/m/Y') }} — {{ optional($contract->end_date)->format('d/m/Y') ?? 'Không xác định' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $contract->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($contract->status === 'expired' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">{{ $statuses[$contract->status] ?? $contract->status }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.contracts.show', $contract) }}" class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-200" title="Xem chi tiết">👁</a>
                                        <a href="{{ route('admin.contracts.edit', $contract) }}" class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center hover:bg-amber-200" title="Sửa">✏️</a>
                                        <form action="{{ route('admin.contracts.destroy', $contract) }}" method="POST" id="delete-form-{{ $contract->id }}" onsubmit="return confirm('Bạn có chắc muốn xóa hợp đồng này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="openDeleteModal('{{ $contract->id }}', @json($contract->contract_code))" class="w-9 h-9 rounded-lg bg-red-100 text-red-600 flex items-center justify-center hover:bg-red-200" title="Xóa mềm">🗑</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">Không có hợp đồng nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($contracts->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    </div>

    <div id="delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-red-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 class="mt-5 text-lg font-bold text-slate-800">Xóa hợp đồng?</h3>
            <p class="mt-2 text-sm text-slate-500">Bạn có chắc muốn xóa hợp đồng <span id="delete-contract-code" class="font-semibold text-slate-700"></span>? Hợp đồng sẽ được chuyển vào thùng rác.</p>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeDeleteModal()" class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">Hủy</button>
                <button type="button" onclick="confirmDelete()" class="flex-1 px-5 py-3 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 transition">Xóa</button>
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
        let deleteTargetId = null;
        function openDeleteModal(id, code) {
            deleteTargetId = id;
            document.getElementById('delete-contract-code').textContent = code;
            const modal = document.getElementById('delete-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function closeDeleteModal() {
            const modal = document.getElementById('delete-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            deleteTargetId = null;
        }
        function confirmDelete() {
            if (deleteTargetId) {
                const form = document.getElementById('delete-form-' + deleteTargetId);
                if (!form) return closeDeleteModal();
                // Use requestSubmit if available so onsubmit handlers run; fallback to submit()
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        }
        document.getElementById('delete-modal').addEventListener('click', function (e) {
            if (e.target === this) closeDeleteModal();
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
