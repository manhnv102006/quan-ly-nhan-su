<x-admin-layout title="Phỏng vấn">
    @php
        $statusLabels = \App\Models\Interview::statusLabels();
        $statusClasses = [
            'scheduled' => 'bg-sky-100 text-sky-800',
            'completed' => 'bg-indigo-100 text-indigo-800',
            'cancelled' => 'bg-slate-100 text-slate-700',
            'no_show' => 'bg-orange-100 text-orange-800',
        ];
        $resultLabels = \App\Models\Interview::resultLabels();
        $resultClasses = [
            'pending' => 'bg-amber-100 text-amber-800',
            'passed' => 'bg-emerald-100 text-emerald-800',
            'failed' => 'bg-rose-100 text-rose-800',
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
        $inputClass = 'w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm normal-case text-slate-800 outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20';
        $labelClass = 'mb-1 block text-xs font-medium normal-case tracking-normal text-slate-600';
    @endphp

    <div class="space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <nav class="mb-1 flex items-center gap-2 text-xs text-slate-500">
                    <a href="{{ route('admin.recruitment') }}" class="hover:text-violet-600">Tuyển dụng</a>
                    <span>/</span>
                    <span class="font-semibold text-slate-700">Phỏng vấn</span>
                </nav>
                <h1 class="text-xl font-bold text-slate-800">Phỏng vấn</h1>
                <p class="mt-0.5 text-xs text-slate-500">
                    Admin chỉ xem lịch và kết quả — quản lý tạo lịch và chấm điểm
                </p>
            </div>
            <x-view-only-badge />
            <a href="{{ route('admin.recruitment.candidates') }}"
               class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:border-violet-300">
                Danh sách ứng viên
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                <p class="font-semibold">Không thể hoàn thành đánh giá:</p>
                <ul class="mt-1 list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            @foreach ([
                ['Tổng', $stats['total'] ?? 0],
                ['Chờ KQ', $stats['pending'] ?? 0],
                ['Đạt', $stats['passed'] ?? 0],
                ['Không đạt', $stats['failed'] ?? 0],
            ] as [$label, $value])
                <div class="rounded-lg border border-slate-100 bg-white px-3 py-2 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</p>
                    <p class="text-lg font-bold text-slate-900">{{ $value }}</p>
                </div>
            @endforeach
        </div>
        <p class="text-[11px] text-slate-400">
            Trạng thái: đã lên lịch {{ $stats['scheduled'] ?? 0 }} · đã PV {{ $stats['completed'] ?? 0 }} · không đến {{ $stats['no_show'] ?? 0 }}
        </p>

        <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm">
            <div class="hidden border-b border-slate-100 bg-slate-50/80 px-4 py-2 text-[11px] font-bold uppercase tracking-wide text-slate-500 md:grid md:grid-cols-12 md:gap-2">
                <span class="md:col-span-3">Ứng viên</span>
                <span class="md:col-span-2">Thời gian</span>
                <span class="md:col-span-2">Phỏng vấn viên</span>
                <span class="md:col-span-2">Trạng thái</span>
                <span class="md:col-span-3 text-right">Thao tác</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($interviews as $interview)
                    @php
                        $statusClass = $statusClasses[$interview->status] ?? 'bg-slate-100 text-slate-700';
                        $resultClass = $resultClasses[$interview->result] ?? 'bg-slate-100 text-slate-700';
                        $openForm = $loop->first && session('success');
                    @endphp

                    <div class="px-4 py-3">
                        <details class="group w-full" @if($openForm) open @endif>
                            <summary class="cursor-pointer list-none marker:content-none [&::-webkit-details-marker]:hidden">
                                <div class="grid grid-cols-1 items-center gap-2 md:grid-cols-12 md:gap-3">
                                    <div class="min-w-0 md:col-span-3">
                                        <p class="truncate text-sm font-semibold text-slate-900">
                                            {{ $interview->candidate?->full_name ?? 'Ứng viên đã xóa' }}
                                        </p>
                                        <p class="truncate text-xs text-slate-500">
                                            {{ $interview->candidate?->jobPost?->title ?? '—' }}
                                        </p>
                                    </div>
                                    <div class="text-sm text-slate-700 md:col-span-2">
                                        {{ $interview->interview_date?->format('d/m/Y H:i') ?? '—' }}
                                    </div>
                                    <div class="truncate text-sm text-slate-600 md:col-span-2">
                                        {{ $interview->interviewer?->full_name ?? 'Quản lý phòng ban' }}
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 md:col-span-2">
                                        <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium {{ $statusClass }}">
                                            {{ $statusLabels[$interview->status] ?? $interview->status }}
                                        </span>
                                        <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium {{ $resultClass }}">
                                            {{ $resultLabels[$interview->result] ?? $interview->result }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3 md:col-span-3 md:justify-end">
                                        @if ($interview->candidate)
                                            <a href="{{ route('admin.recruitment.candidates.show', $interview->candidate) }}"
                                               class="text-sm font-semibold text-violet-700 hover:text-violet-900"
                                               onclick="event.stopPropagation()">
                                                Hồ sơ
                                            </a>
                                        @endif
                                        <span class="inline-flex items-center gap-1 text-sm font-semibold text-violet-700 group-open:text-violet-900">
                                            Xem chi tiết
                                            <svg class="h-4 w-4 shrink-0 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                        </span>
                                    </div>
                                </div>
                            </summary>

                            <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                @include('recruitment.partials.interview-readonly', ['interview' => $interview])
                            </div>
                        </details>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center">
                        <p class="text-sm font-semibold text-slate-700">Chưa có lịch phỏng vấn</p>
                        <p class="mt-1 text-xs text-slate-500">Quản lý tạo lịch từ hồ sơ ứng viên phòng ban.</p>
                        <a href="{{ route('admin.recruitment.candidates') }}" class="mt-3 inline-flex rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold text-white hover:bg-violet-700">
                            Xem ứng viên
                        </a>
                    </div>
                @endforelse
            </div>

            @if ($interviews->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">
                    {{ $interviews->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
