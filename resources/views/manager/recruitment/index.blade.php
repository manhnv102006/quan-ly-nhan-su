<x-manager-layout
    title="Tuyển dụng phòng ban"
    subtitle="Theo dõi tin đang mở và cập nhật kết quả phỏng vấn thuộc phạm vi quản lý của bạn."
>
    @php
        $statusLabels = [
            'scheduled' => 'Đã lên lịch',
            'completed' => 'Đã phỏng vấn',
            'cancelled' => 'Đã hủy',
            'no_show' => 'Không đến',
        ];
        $statusClasses = [
            'scheduled' => 'bg-sky-100 text-sky-800',
            'completed' => 'bg-indigo-100 text-indigo-800',
            'cancelled' => 'bg-slate-100 text-slate-700',
            'no_show' => 'bg-orange-100 text-orange-800',
        ];
        $resultLabels = [
            'pending' => 'Chờ kết quả',
            'passed' => 'Đạt',
            'failed' => 'Không đạt',
        ];
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
        $inputClass = 'w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20';
        $labelClass = 'mb-1 block text-xs font-medium text-slate-600';
        $jobStatusLabels = [
            'open' => 'Đang tuyển',
            'closed' => 'Đã đóng',
            'pending_approval' => 'Chờ admin duyệt',
            'rejected' => 'Admin từ chối',
        ];
        $jobStatusClasses = [
            'open' => 'bg-emerald-100 text-emerald-800',
            'closed' => 'bg-slate-100 text-slate-700',
            'pending_approval' => 'bg-amber-100 text-amber-800',
            'rejected' => 'bg-rose-100 text-rose-800',
        ];
    @endphp

    <div class="manager-page space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">Không thể hoàn thành đánh giá:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! $manager)
            <div class="manager-card p-8 text-center text-sm text-slate-600">
                Tài khoản chưa liên kết hồ sơ nhân viên quản lý. Vui lòng liên hệ admin.
            </div>
        @else
            <section class="manager-card p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Tin tuyển dụng phòng ban</h2>
                        <p class="mt-1 text-sm text-slate-500">Theo dõi tin đang mở, chờ duyệt và lịch phỏng vấn.</p>
                    </div>
                    <a href="{{ route('manager.recruitment.job-posts.create') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700">
                        + Tạo tin tuyển dụng
                    </a>
                </div>
                <ul class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-100">
                    @forelse ($departmentJobPosts as $jobPost)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $jobPost->title }}</p>
                                <p class="text-slate-500">
                                    {{ $jobPost->department?->department_name ?? '—' }}
                                    · {{ $jobPost->quantity }} chỉ tiêu
                                    @if ($jobPost->status === 'open')
                                        · Còn tuyển công khai
                                    @endif
                                </p>
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $jobStatusClasses[$jobPost->status] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $jobStatusLabels[$jobPost->status] ?? $jobPost->status }}
                            </span>
                        </li>
                    @empty
                        <li class="px-4 py-8 text-center text-sm text-slate-500">Chưa có tin tuyển dụng nào.</li>
                    @endforelse
                </ul>
            </section>

            <section class="manager-card p-6">
                <h2 class="text-lg font-bold text-slate-900">Lịch phỏng vấn</h2>
                <p class="mt-1 text-sm text-slate-500">Lịch do admin tạo từ hồ sơ ứng viên · mới nhất ở trên.</p>

                <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach ([
                        ['Tổng', $stats['total'] ?? 0],
                        ['Chờ KQ', $stats['pending'] ?? 0],
                        ['Đạt', $stats['passed'] ?? 0],
                        ['Không đạt', $stats['failed'] ?? 0],
                    ] as [$label, $value])
                        <div class="rounded-lg border border-slate-100 bg-slate-50/80 px-3 py-2">
                            <p class="text-[11px] font-semibold uppercase text-slate-400">{{ $label }}</p>
                            <p class="text-lg font-bold text-slate-900">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 overflow-hidden rounded-xl border border-slate-100">
                    <div class="divide-y divide-slate-100">
                        @forelse ($interviews as $interview)
                            @php
                                $statusClass = $statusClasses[$interview->status] ?? 'bg-slate-100 text-slate-700';
                                $resultClass = $resultClasses[$interview->result] ?? 'bg-slate-100 text-slate-700';
                                $openForm = $loop->first && session('success');
                            @endphp
                            <div class="px-4 py-3">
                                <details class="group w-full" @if($openForm) open @endif>
                                    <summary class="cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                        <div class="grid grid-cols-1 items-center gap-2 md:grid-cols-12 md:gap-3">
                                            <div class="min-w-0 md:col-span-4">
                                                <p class="truncate text-sm font-semibold text-slate-900">{{ $interview->candidate?->full_name ?? '—' }}</p>
                                                <p class="truncate text-xs text-slate-500">{{ $interview->candidate?->jobPost?->title ?? '—' }}</p>
                                            </div>
                                            <div class="text-sm text-slate-700 md:col-span-3">{{ $interview->interview_date?->format('d/m/Y H:i') ?? '—' }}</div>
                                            <div class="flex flex-wrap gap-1.5 md:col-span-3">
                                                <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ $statusLabels[$interview->status] ?? $interview->status }}</span>
                                                <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium {{ $resultClass }}">{{ $resultLabels[$interview->result] ?? $interview->result }}</span>
                                            </div>
                                            <div class="md:col-span-2 md:text-right">
                                                <span class="inline-flex items-center gap-1 text-sm font-semibold text-teal-700 group-open:text-teal-900">
                                                    Cập nhật
                                                    <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                                </span>
                                            </div>
                                        </div>
                                    </summary>

                                    <form action="{{ route('manager.recruitment.interviews.update', $interview) }}" method="POST" data-interview-evaluation class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                            <div>
                                                <label class="{{ $labelClass }}">Trạng thái</label>
                                                <select name="status" required class="{{ $inputClass }}">
                                                    @foreach ($statusLabels as $value => $text)
                                                        <option value="{{ $value }}" @selected($interview->status === $value)>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }}">Kết quả</label>
                                                <select name="result" required class="{{ $inputClass }}">
                                                    @foreach ($resultLabels as $value => $text)
                                                        <option value="{{ $value }}" @selected($interview->result === $value)>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="{{ $labelClass }}">Đề xuất</label>
                                                <select name="recommendation" class="{{ $inputClass }}">
                                                    @foreach ($recommendationLabels as $value => $text)
                                                        <option value="{{ $value }}" @selected((string) $interview->recommendation === (string) $value)>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                            <p class="col-span-full text-xs text-slate-500">
                                                Bắt buộc nhập đủ 4 tiêu chí khi trạng thái <strong>Đã phỏng vấn</strong> hoặc kết quả <strong>Đạt / Không đạt</strong>.
                                            </p>
                                            @foreach ($scoreFields as $field => $scoreLabel)
                                                <div>
                                                    <label class="{{ $labelClass }}">{{ $scoreLabel }} (0–10) <span class="text-red-600">*</span></label>
                                                    <input type="number" min="0" max="10" step="1" name="{{ $field }}" value="{{ old($field, $interview->{$field}) }}" class="{{ $inputClass }} @error($field) border-red-400 @enderror">
                                                    @error($field)
                                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-3">
                                            <label class="{{ $labelClass }}">Ghi chú</label>
                                            <textarea name="note" rows="2" class="{{ $inputClass }} resize-y">{{ $interview->note }}</textarea>
                                        </div>
                                        <div class="mt-4 flex justify-end">
                                            <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Lưu kết quả</button>
                                        </div>
                                    </form>
                                </details>
                            </div>
                        @empty
                            <div class="px-4 py-10 text-center text-sm text-slate-500">Chưa có lịch phỏng vấn trong phạm vi phòng ban của bạn.</div>
                        @endforelse
                    </div>
                    @if ($interviews->hasPages())
                        <div class="border-t border-slate-100 px-4 py-3">{{ $interviews->links() }}</div>
                    @endif
                </div>
            </section>
        @endif
    </div>

    @include('recruitment.partials.interview-score-validation')
</x-manager-layout>
