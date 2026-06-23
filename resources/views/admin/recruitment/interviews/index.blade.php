<x-admin-layout title="Lịch phỏng vấn">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.recruitment') }}" class="hover:text-amber-600 transition">Tuyển dụng</a>
                    <span>/</span>
                    <span class="text-slate-700 font-medium">Lịch phỏng vấn</span>
                </div>

                <h2 class="mt-2 text-2xl font-bold text-slate-800">Danh sách lịch phỏng vấn</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Theo dõi toàn bộ lịch phỏng vấn hiện có, ứng viên liên quan và cập nhật kết quả ngay trên từng lịch.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.recruitment.interviews.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-amber-600 text-white font-medium hover:bg-amber-700 transition shadow-lg shadow-amber-500/20">
                    + Tạo lịch phỏng vấn
                </a>

                <a href="{{ route('admin.recruitment') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                    Quay lại tuyển dụng
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="flex items-center gap-3 bg-white border border-emerald-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 bg-white border border-red-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Tổng lịch phỏng vấn</p>
                <h3 class="text-3xl font-bold mt-2 text-slate-900">{{ $stats['total'] }}</h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Đang chờ kết quả</p>
                <h3 class="text-3xl font-bold mt-2 text-amber-600">{{ $stats['pending'] }}</h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Đạt</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">{{ $stats['passed'] }}</h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Không đạt</p>
                <h3 class="text-3xl font-bold mt-2 text-rose-600">{{ $stats['failed'] }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="font-semibold text-slate-800">Danh sách lịch phỏng vấn</h3>
                <p class="text-sm text-slate-500">Hiển thị {{ $interviews->count() }} / {{ $interviews->total() }} bản ghi</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Ứng viên</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Tin tuyển dụng</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Người phỏng vấn</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Thời gian</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Cập nhật kết quả</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($interviews as $interview)
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition align-top">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800">{{ $interview->candidate?->full_name ?? 'Ứng viên không tồn tại' }}</div>
                                    <p class="mt-1 text-sm text-slate-500">{{ $interview->candidate?->phone ?? 'Không có số điện thoại' }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $interview->candidate?->jobPost?->title ?? 'Chưa gắn tin tuyển dụng' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $interview->interviewer?->full_name ?? 'Chưa phân công' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $interview->interview_date?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="px-6 py-4 min-w-[320px]">
                                    <form action="{{ route('admin.recruitment.interviews.update', $interview) }}" method="POST" class="space-y-3">
                                        @csrf
                                        @method('PUT')

                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Kết quả</label>
                                            <select name="result"
                                                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none">
                                                <option value="pending" @selected($interview->result === 'pending')>Đang chờ</option>
                                                <option value="passed" @selected($interview->result === 'passed')>Đạt</option>
                                                <option value="failed" @selected($interview->result === 'failed')>Không đạt</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Ghi chú</label>
                                            <textarea name="note" rows="3"
                                                      class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none">{{ old('note', $interview->note) }}</textarea>
                                        </div>

                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                @if ($interview->result === 'pending')
                                                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Đang chờ</span>
                                                @elseif ($interview->result === 'passed')
                                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Đạt</span>
                                                @else
                                                    <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Không đạt</span>
                                                @endif
                                            </div>

                                            <button type="submit"
                                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 text-white text-sm font-medium hover:bg-amber-700 transition">
                                                Lưu kết quả
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-slate-400">Chưa có lịch phỏng vấn nào trong hệ thống.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($interviews->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $interviews->links() }}
                </div>
            @endif
        </div>

    </div>

</x-admin-layout>