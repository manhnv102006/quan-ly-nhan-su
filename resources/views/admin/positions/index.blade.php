<x-admin-layout title="Quản lý chức vụ">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Danh sách chức vụ</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Tổng cộng {{ $stats['total'] }} chức vụ
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.positions.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                    <span>+</span>
                    Thêm chức vụ
                </a>

                <a href="{{ route('admin.positions.trash') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                    Chức vụ đã xóa
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Tổng chức vụ</p>
                <h3 class="text-3xl font-bold mt-2">{{ $stats['total'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Đang hoạt động</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">{{ $stats['active'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Có nhân viên</p>
                <h3 class="text-3xl font-bold mt-2 text-violet-600">{{ $stats['with_employees'] }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800">Danh sách chức vụ</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tên chức vụ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Lương cơ bản</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mô tả</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Ngày tạo</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($positions as $position)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600">{{ $position->id }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $position->position_name }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg bg-violet-50 text-violet-700 text-sm font-medium">
                                        {{ number_format($position->base_salary, 0, ',', '.') }} ₫
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500 max-w-xs truncate">
                                    {{ $position->description ?: '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($position->status === 'active')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Hoạt động</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Không hoạt động</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ $position->created_at?->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('admin.positions.show', $position) }}"
                                           class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-200"
                                           title="Xem chi tiết">👁</a>

                                        <a href="{{ route('admin.positions.edit', $position) }}"
                                           class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center hover:bg-amber-200"
                                           title="Sửa">✏️</a>

                                        <form action="{{ route('admin.positions.destroy', $position) }}"
                                              method="POST"
                                              id="delete-form-{{ $position->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    onclick="openDeleteModal('{{ $position->id }}', @json($position->position_name))"
                                                    class="w-9 h-9 rounded-lg bg-red-100 text-red-600 flex items-center justify-center hover:bg-red-200"
                                                    title="Xóa mềm">🗑</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">
                                    Chưa có chức vụ nào
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($positions->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $positions->links() }}
                </div>
            @endif
        </div>

    </div>

    <div id="delete-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-red-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 class="mt-5 text-lg font-bold text-slate-800">Xóa chức vụ?</h3>
            <p class="mt-2 text-sm text-slate-500">
                Bạn có chắc muốn xóa chức vụ
                <span id="delete-position-name" class="font-semibold text-slate-700"></span>?
                Chức vụ sẽ được chuyển vào danh sách đã xóa và có thể khôi phục sau.
            </p>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="button" onclick="confirmDelete()"
                        class="flex-1 px-5 py-3 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 transition">
                    Xóa
                </button>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div id="success-toast"
             class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-emerald-200 shadow-lg rounded-2xl px-5 py-4 max-w-sm">
            <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">{{ session('success') }}</p>
        </div>
    @endif

    <script>
        let deleteTargetId = null;

        function openDeleteModal(id, name) {
            deleteTargetId = id;
            document.getElementById('delete-position-name').textContent = name;
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
                document.getElementById('delete-form-' + deleteTargetId).submit();
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
