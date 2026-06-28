<x-admin-layout title="Phỏng vấn">
    @php
        $statusLabels = [
            'scheduled' => 'Đã lên lịch',
            'completed' => 'Đã phỏng vấn',
            'cancelled' => 'Đã hủy',
            'no_show' => 'Không đến',
        ];
        $statusClasses = [
            'scheduled' => 'bg-sky-100 text-sky-700 ring-sky-200',
            'completed' => 'bg-indigo-100 text-indigo-700 ring-indigo-200',
            'cancelled' => 'bg-slate-100 text-slate-700 ring-slate-200',
            'no_show' => 'bg-orange-100 text-orange-700 ring-orange-200',
        ];
        $resultLabels = [
            'pending' => 'Chờ kết quả',
            'passed' => 'Đạt',
            'failed' => 'Không đạt',
        ];
        $resultClasses = [
            'pending' => 'bg-amber-100 text-amber-700 ring-amber-200',
            'passed' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'failed' => 'bg-rose-100 text-rose-700 ring-rose-200',
        ];
        $recommendationLabels = [
            '' => 'Chưa chọn',
            'hire' => 'Nên tuyển',
            'consider' => 'Cần cân nhắc',
            'reject' => 'Từ chối',
        ];
        $scoreFields = [
            'overall_score' => 'Tổng quan',
            'technical_score' => 'Kỹ thuật',
            'attitude_score' => 'Thái độ',
            'culture_score' => 'Văn hóa',
        ];
        $summaryStats = [
            ['label' => 'Tổng lịch', 'value' => $stats['total'] ?? 0, 'class' => 'from-slate-700 to-slate-900 text-white'],
            ['label' => 'Chờ kết quả', 'value' => $stats['pending'] ?? 0, 'class' => 'from-amber-500 to-orange-500 text-white'],
            ['label' => 'Đạt', 'value' => $stats['passed'] ?? 0, 'class' => 'from-emerald-500 to-teal-500 text-white'],
            ['label' => 'Không đạt', 'value' => $stats['failed'] ?? 0, 'class' => 'from-rose-500 to-pink-500 text-white'],
        ];
    @endphp

    @include('admin.recruitment.partials.ui-contrast')

    <div class="recruitment-ui max-w-full overflow-hidden space-y-6">
        <div class="recruitment-hero rounded-[2rem] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/60 backdrop-blur sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600 transition">Tuyển dụng</a>
                        <span>/</span>
                        <span class="font-medium text-slate-700">Phỏng vấn</span>
                    </div>
                    <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Quản lý phỏng vấn</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Theo dõi lịch phỏng vấn, cập nhật kết quả và điểm đánh giá ứng viên trong một màn hình gọn hơn.
                    </p>
                </div>

                <a href="{{ route('admin.recruitment.interviews.create') }}"
                   class="recruitment-btn-primary inline-flex w-full items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700 sm:w-auto">
                    Tạo lịch phỏng vấn
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <p class="font-semibold">Vui lòng kiểm tra lại thông tin phỏng vấn:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="recruitment-stats grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($summaryStats as $item)
                <div class="overflow-hidden rounded-[1.75rem] bg-gradient-to-br {{ $item['class'] }} p-5 shadow-sm">
                    <p class="text-sm font-semibold opacity-85">{{ $item['label'] }}</p>
                    <p class="mt-3 text-3xl font-extrabold">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="recruitment-panel rounded-[1.75rem] border border-slate-100 bg-white/85 p-4 shadow-sm">
            <div class="grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
                <div class="min-w-0 rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="truncate font-semibold text-slate-700">Đã lên lịch</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">{{ $stats['scheduled'] ?? 0 }}</p>
                </div>
                <div class="min-w-0 rounded-2xl bg-indigo-50 px-4 py-3">
                    <p class="truncate font-semibold text-indigo-700">Đã phỏng vấn</p>
                    <p class="mt-1 text-xl font-bold text-indigo-800">{{ $stats['completed'] ?? 0 }}</p>
                </div>
                <div class="min-w-0 rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="truncate font-semibold text-slate-700">Đã hủy</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">{{ $stats['cancelled'] ?? 0 }}</p>
                </div>
                <div class="min-w-0 rounded-2xl bg-orange-50 px-4 py-3">
                    <p class="truncate font-semibold text-orange-700">Không đến</p>
                    <p class="mt-1 text-xl font-bold text-orange-800">{{ $stats['no_show'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($interviews as $interview)
                @php
                    $statusClass = $statusClasses[$interview->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
                    $resultClass = $resultClasses[$interview->result] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
                @endphp

                <article class="recruitment-panel max-w-full overflow-hidden rounded-[1.75rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/50">
                    <div class="grid grid-cols-1 xl:grid-cols-12">
                        <div class="min-w-0 border-b border-slate-100 bg-slate-50/80 p-5 xl:col-span-4 xl:border-b-0 xl:border-r">
                            <div class="flex min-w-0 flex-col gap-4">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClass }}">
                                            {{ $statusLabels[$interview->status] ?? $interview->status }}
                                        </span>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $resultClass }}">
                                            {{ $resultLabels[$interview->result] ?? $interview->result }}
                                        </span>
                                    </div>

                                    <h3 class="mt-4 break-words text-lg font-bold leading-7 text-slate-900">
                                        {{ $interview->candidate?->full_name ?? 'Ứng viên đã xóa' }}
                                    </h3>
                                    <p class="mt-1 break-words text-sm leading-6 text-slate-500">
                                        {{ $interview->candidate?->jobPost?->title ?? 'Chưa gắn tin tuyển dụng' }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 xl:grid-cols-1">
                                    <div class="min-w-0 rounded-2xl bg-white px-4 py-3 ring-1 ring-slate-100">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Thời gian</p>
                                        <p class="mt-1 break-words font-semibold text-slate-700">{{ $interview->interview_date?->format('d/m/Y H:i') ?? '-' }}</p>
                                    </div>
                                    <div class="min-w-0 rounded-2xl bg-white px-4 py-3 ring-1 ring-slate-100">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Người phỏng vấn</p>
                                        <p class="mt-1 break-words font-semibold text-slate-700">{{ $interview->interviewer?->full_name ?? 'Chưa gắn' }}</p>
                                    </div>
                                    <div class="min-w-0 rounded-2xl bg-white px-4 py-3 ring-1 ring-slate-100">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Đề xuất hiện tại</p>
                                        <p class="mt-1 break-words font-semibold text-slate-700">{{ $recommendationLabels[$interview->recommendation ?? ''] ?? $interview->recommendation }}</p>
                                    </div>
                                    <div class="min-w-0 rounded-2xl bg-white px-4 py-3 ring-1 ring-slate-100">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Điểm tổng quan</p>
                                        <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ $interview->overall_score ?? '-' }}<span class="text-sm font-semibold text-slate-400">/10</span></p>
                                    </div>
                                </div>

                                @if ($interview->candidate)
                                    <a href="{{ route('admin.recruitment.candidates.show', $interview->candidate) }}"
                                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700">
                                        Xem hồ sơ ứng viên
                                    </a>
                                @endif
                            </div>
                        </div>

                        <form action="{{ route('admin.recruitment.interviews.update', $interview) }}" method="POST" class="min-w-0 p-5 xl:col-span-8">
                            @csrf
                            @method('PUT')

                            <div class="grid min-w-0 grid-cols-1 gap-4 lg:grid-cols-3">
                                <div class="min-w-0">
                                    <label class="mb-2 block text-sm font-semibold text-slate-700">Trạng thái</label>
                                    <select name="status" required class="w-full min-w-0 rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                                        @foreach ($statusLabels as $value => $label)
                                            <option value="{{ $value }}" @selected($interview->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="min-w-0">
                                    <label class="mb-2 block text-sm font-semibold text-slate-700">Kết quả</label>
                                    <select name="result" required class="w-full min-w-0 rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                                        @foreach ($resultLabels as $value => $label)
                                            <option value="{{ $value }}" @selected($interview->result === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="min-w-0">
                                    <label class="mb-2 block text-sm font-semibold text-slate-700">Đề xuất</label>
                                    <select name="recommendation" class="w-full min-w-0 rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                                        @foreach ($recommendationLabels as $value => $label)
                                            <option value="{{ $value }}" @selected((string) $interview->recommendation === (string) $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 grid min-w-0 grid-cols-2 gap-3 lg:grid-cols-4">
                                @foreach ($scoreFields as $field => $label)
                                    <div class="min-w-0">
                                        <label class="mb-2 block truncate text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</label>
                                        <input type="number" min="0" max="10" name="{{ $field }}" value="{{ $interview->{$field} }}"
                                               class="w-full min-w-0 rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                                    </div>
                                @endforeach
                            </div>

                            <details class="mt-4 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                <summary class="cursor-pointer select-none text-sm font-semibold text-slate-700">
                                    Ghi chú và đánh giá chi tiết
                                </summary>

                                <div class="mt-4 grid min-w-0 grid-cols-1 gap-4 lg:grid-cols-3">
                                    <div class="min-w-0 lg:col-span-3">
                                        <label class="mb-2 block text-sm font-semibold text-slate-700">Ghi chú chung</label>
                                        <textarea name="note" rows="2" class="w-full min-w-0 rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">{{ $interview->note }}</textarea>
                                    </div>

                                    <div class="min-w-0">
                                        <label class="mb-2 block text-sm font-semibold text-slate-700">Điểm mạnh</label>
                                        <textarea name="strengths" rows="3" class="w-full min-w-0 rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">{{ $interview->strengths }}</textarea>
                                    </div>

                                    <div class="min-w-0 lg:col-span-2">
                                        <label class="mb-2 block text-sm font-semibold text-slate-700">Điểm cần cải thiện</label>
                                        <textarea name="weaknesses" rows="3" class="w-full min-w-0 rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">{{ $interview->weaknesses }}</textarea>
                                    </div>
                                </div>
                            </details>

                            <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs leading-5 text-slate-500">
                                    Khi kết quả là Đạt hoặc Không đạt, trạng thái ứng viên sẽ được cập nhật tự động.
                                </p>
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700 sm:w-auto">
                                    Lưu kết quả
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            @empty
                <div class="recruitment-panel rounded-[1.75rem] border border-dashed border-slate-200 bg-white/80 px-5 py-14 text-center text-sm text-slate-500 shadow-sm">
                    Chưa có lịch phỏng vấn nào.
                </div>
            @endforelse
        </div>

        <div class="recruitment-panel max-w-full overflow-x-auto rounded-[1.75rem] border border-slate-100 bg-white p-4 shadow-sm">
            {{ $interviews->links() }}
        </div>
    </div>
</x-admin-layout>
