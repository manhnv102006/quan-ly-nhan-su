<x-admin-layout title="Chi tiết hợp đồng">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Chi tiết hợp đồng</h2>
                <p class="text-sm text-slate-500 mt-1">Thông tin chi tiết hợp đồng và lịch sử.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.contracts.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">Quay lại danh sách</a>
                <a href="{{ route('admin.contracts.edit', $contract) }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-amber-100 text-amber-700 font-medium hover:bg-amber-200 transition">Chỉnh sửa hợp đồng</a>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 xl:col-span-2">
                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-slate-400">Mã hợp đồng</p>
                        <p class="mt-2 text-lg font-semibold text-slate-800">{{ $contract->contract_code }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-slate-400">Nhân viên</p>
                        <p class="mt-2 text-lg font-semibold text-slate-800">{{ $contract->employee->full_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-slate-400">Loại hợp đồng</p>
                        <p class="mt-2 text-lg font-semibold text-slate-800">{{ $contract->contractType->contract_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-slate-400">Trạng thái</p>
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ $contract->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($contract->status === 'expired' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">{{ $contract->status_label }}</span>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div class="rounded-3xl bg-slate-50 p-5 border border-slate-100">
                        <p class="text-xs uppercase tracking-widest text-slate-400">Ngày bắt đầu</p>
                        <p class="mt-3 text-lg font-semibold text-slate-800">{{ optional($contract->start_date)->format('d/m/Y') }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-5 border border-slate-100">
                        <p class="text-xs uppercase tracking-widest text-slate-400">Ngày kết thúc</p>
                        <p class="mt-3 text-lg font-semibold text-slate-800">{{ optional($contract->end_date)->format('d/m/Y') ?? 'Không xác định' }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div class="rounded-3xl bg-slate-50 p-5 border border-slate-100">
                        <p class="text-xs uppercase tracking-widest text-slate-400">Ngày ký</p>
                        <p class="mt-3 text-lg font-semibold text-slate-800">{{ optional($contract->signed_date)->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-5 border border-slate-100">
                        <p class="text-xs uppercase tracking-widest text-slate-400">Lương</p>
                        <p class="mt-3 text-lg font-semibold text-slate-800">{{ number_format($contract->salary, 0, ',', '.') }} ₫</p>
                    </div>
                </div>

                @if($contract->note)
                    <div class="mt-6 rounded-3xl bg-slate-50 p-5 border border-slate-100">
                        <p class="text-xs uppercase tracking-widest text-slate-400">Ghi chú</p>
                        <p class="mt-3 text-sm text-slate-700 whitespace-pre-line">{{ $contract->note }}</p>
                    </div>
                @endif

                <div class="mt-6 rounded-3xl bg-slate-50 p-5 border border-slate-100">
                    <p class="text-xs uppercase tracking-widest text-slate-400">Tệp hợp đồng</p>
                    @if($contract->file_path)
                        <p class="mt-3 text-sm text-slate-700"><a href="{{ asset('storage/'.$contract->file_path) }}" target="_blank" class="text-violet-600 hover:underline">Tải xuống / Xem tệp</a></p>
                    @else
                        <p class="mt-3 text-sm text-slate-500">Chưa có tệp đính kèm.</p>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-semibold text-slate-800">Gia hạn hợp đồng</h3>
                    <form action="{{ route('admin.contracts.extend', $contract) }}" method="POST" class="space-y-4 mt-4">
                        @csrf
                        <div>
                            <label for="new_end_date" class="block text-sm font-semibold text-slate-700">Ngày kết thúc mới</label>
                            <input id="new_end_date" name="new_end_date" type="date" value="{{ old('new_end_date') }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none" required>
                            @error('new_end_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="extend_note" class="block text-sm font-semibold text-slate-700">Ghi chú</label>
                            <textarea id="extend_note" name="note" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none">{{ old('note') }}</textarea>
                            @error('note') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center w-full px-5 py-3 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition">Gia hạn hợp đồng</button>
                    </form>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-semibold text-slate-800">Thanh lý hợp đồng</h3>
                    <form action="{{ route('admin.contracts.terminate', $contract) }}" method="POST" enctype="multipart/form-data" class="space-y-4 mt-4">
                        @csrf
                        <div>
                            <label for="terminate_end_date" class="block text-sm font-semibold text-slate-700">Ngày thanh lý</label>
                            <input id="terminate_end_date" name="end_date" type="date" value="{{ old('end_date') }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none" required>
                            @error('end_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="terminate_file" class="block text-sm font-semibold text-slate-700">Biên bản thanh lý</label>
                            <input id="terminate_file" name="file" type="file" class="mt-2 w-full text-sm text-slate-700">
                            @error('file') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="terminate_note" class="block text-sm font-semibold text-slate-700">Ghi chú</label>
                            <textarea id="terminate_note" name="note" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none">{{ old('note') }}</textarea>
                            @error('note') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center w-full px-5 py-3 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 transition">Thanh lý hợp đồng</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-semibold text-slate-800">Lịch sử gia hạn</h3>
            <div class="mt-4 space-y-4">
                @forelse($contract->extensions as $extension)
                    <div class="rounded-3xl bg-slate-50 p-4 border border-slate-100">
                        <p class="text-sm font-semibold text-slate-800">{{ optional($extension->created_at)->format('d/m/Y H:i') }}</p>
                        <p class="text-sm text-slate-600">Từ {{ optional($extension->old_end_date)->format('d/m/Y') ?? '—' }} đến {{ optional($extension->new_end_date)->format('d/m/Y') }}</p>
                        <p class="text-sm text-slate-500 mt-2">{{ $extension->note ?: 'Không có ghi chú' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Chưa có lịch sử gia hạn.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-semibold text-slate-800">Lịch sử thanh lý</h3>
            <div class="mt-4 space-y-4">
                @forelse($contract->terminations as $termination)
                    <div class="rounded-3xl bg-slate-50 p-4 border border-slate-100">
                        <p class="text-sm font-semibold text-slate-800">{{ optional($termination->created_at)->format('d/m/Y H:i') }}</p>
                        <p class="text-sm text-slate-600">Ngày thanh lý: {{ optional($termination->end_date)->format('d/m/Y') }}</p>
                        <p class="text-sm text-slate-500 mt-2">{{ $termination->note ?: 'Không có ghi chú' }}</p>
                        @if($termination->file_path)
                            <p class="mt-3 text-sm text-violet-600"><a href="{{ asset('storage/'.$termination->file_path) }}" target="_blank" class="hover:underline">Tải biên bản</a></p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Chưa có lịch sử thanh lý.</p>
                @endforelse
            </div>
        </div>
    </div>

    @if(session('success'))
        <div id="success-toast" class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-emerald-200 shadow-lg rounded-2xl px-5 py-4 max-w-sm">
            <p class="text-sm font-medium text-slate-700">{{ session('success') }}</p>
        </div>
        <script>
            setTimeout(function () {
                const toast = document.getElementById('success-toast');
                if (toast) toast.remove();
            }, 4000);
        </script>
    @endif
</x-admin-layout>
