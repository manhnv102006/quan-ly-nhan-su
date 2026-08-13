<x-manager-layout
    title="Tuyển dụng phòng ban"
    subtitle="Theo dõi tin đang mở và cập nhật kết quả phỏng vấn thuộc phạm vi quản lý của bạn."
>
    @php
        $statusLabels = \App\Models\Interview::statusLabels();
        $editableStatusLabels = collect($statusLabels)->only(\App\Models\Interview::EDITABLE_STATUSES)->all();
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
        $recommendationLabels = \App\Models\Interview::recommendationLabels();
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
                        <h2 class="text-lg font-bold text-slate-900">Ứng viên phòng ban</h2>
                        <p class="mt-1 text-sm text-slate-500">Ứng viên nộp hồ sơ vào tin tuyển dụng thuộc phòng ban bạn quản lý.</p>
                    </div>
                    <a href="{{ route('manager.recruitment.candidates.index') }}"
                       class="inline-flex items-center justify-center rounded-xl border border-teal-200 bg-teal-50 px-5 py-2.5 text-sm font-semibold text-teal-800 hover:bg-teal-100">
                        Xem danh sách ứng viên
                    </a>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach ([
                        ['Tổng', $candidateStats['total'] ?? 0],
                        ['Mới', $candidateStats['new'] ?? 0],
                        ['Phỏng vấn', $candidateStats['interview'] ?? 0],
                        ['Chờ admin', $candidateStats['pending_hire_approval'] ?? 0],
                    ] as [$label, $value])
                        <div class="rounded-lg border border-slate-100 bg-slate-50/80 px-3 py-2">
                            <p class="text-[11px] font-semibold uppercase text-slate-400">{{ $label }}</p>
                            <p class="text-lg font-bold text-slate-900">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

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
                <p class="mt-1 text-sm text-slate-500">Tạo lịch từ hồ sơ ứng viên · chấm điểm và gửi kết quả cho Admin duyệt.</p>

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
                                $canManagerUpdate = ! $interview->isLockedForManager();
                                $openForm = $canManagerUpdate && $loop->first && session('success');
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
                                                <span class="inline-flex items-center gap-1 text-sm font-semibold {{ $canManagerUpdate ? 'text-teal-700 group-open:text-teal-900' : 'text-slate-500' }}">
                                                    {{ $canManagerUpdate ? 'Cập nhật' : 'Xem chi tiết' }}
                                                    <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                                </span>
                                            </div>
                                        </div>
                                    </summary>

                                    @if ($canManagerUpdate)
                                    <form action="{{ route('manager.recruitment.interviews.update', $interview) }}" method="POST" data-interview-evaluation class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="{{ $labelClass }}">Trạng thái</label>
                                            <select name="status" required class="{{ $inputClass }}">
                                                @php
                                                    $currentStatus = in_array($interview->status, \App\Models\Interview::EDITABLE_STATUSES, true)
                                                        ? $interview->status
                                                        : \App\Models\Interview::STATUS_SCHEDULED;
                                                @endphp
                                                @foreach ($editableStatusLabels as $value => $text)
                                                    <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <p data-interview-no-show-hint
                                           @class(['mt-3 rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 text-xs text-orange-800', 'hidden' => $currentStatus !== 'no_show'])>
                                            Ứng viên không tham dự — chỉ cần lưu trạng thái, không cần chấm điểm hay nhập kết quả.
                                        </p>

                                        <p data-interview-scheduled-hint
                                           @class(['mt-3 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs text-sky-800', 'hidden' => $currentStatus !== 'scheduled'])>
                                            Buổi phỏng vấn đã lên lịch — kết quả luôn là <strong>Chờ kết quả</strong> và chưa được chấm điểm.
                                        </p>

                                        <div data-interview-evaluation-panel @class(['mt-3 space-y-3', 'hidden' => $currentStatus === 'no_show'])>
                                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <div data-interview-result-row>
                                                    <label class="{{ $labelClass }}">Kết quả</label>
                                                    <select name="result" class="{{ $inputClass }}">
                                                        @if ($currentStatus === 'scheduled')
                                                            <option value="pending" selected>Chờ kết quả</option>
                                                        @else
                                                            <option value="passed" @selected($interview->result === 'passed')>Đạt</option>
                                                            <option value="failed" @selected($interview->result === 'failed')>Không đạt</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                <div data-interview-recommendation-row @class(['hidden' => $currentStatus !== 'completed'])>
                                                    <label class="{{ $labelClass }}">Đề xuất</label>
                                                    <select name="recommendation" class="{{ $inputClass }}">
                                                        @if ($interview->result === 'failed')
                                                            <option value="reject" selected>Từ chối</option>
                                                        @else
                                                            <option value="hire" @selected($interview->recommendation === 'hire')>Nên tuyển</option>
                                                            <option value="consider" @selected($interview->recommendation === 'consider')>Cần cân nhắc</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                            <div data-interview-score-section @class(['grid grid-cols-2 gap-3 sm:grid-cols-4', 'hidden' => $currentStatus !== 'completed'])>
                                                <p class="col-span-full text-xs text-slate-500">
                                                    Bắt buộc nhập đủ 4 tiêu chí khi trạng thái là <strong>Đã phỏng vấn</strong>.
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
                                            <div data-interview-note-section @class(['hidden' => $currentStatus !== 'completed'])>
                                                <label class="{{ $labelClass }}">Ghi chú</label>
                                                <textarea name="note" rows="2" class="{{ $inputClass }} resize-y">{{ $interview->note }}</textarea>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex justify-end">
                                            <button type="submit"
                                                    data-interview-submit
                                                    class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                                                @if (in_array($currentStatus, ['no_show', 'scheduled'], true))
                                                    Lưu trạng thái
                                                @else
                                                    Gửi kết quả cho Admin
                                                @endif
                                            </button>
                                        </div>
                                    </form>
                                    @else
                                    <div class="mt-3">
                                        @include('recruitment.partials.interview-readonly', ['interview' => $interview])
                                        <p class="mt-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600">
                                            Kết quả phỏng vấn đã được gửi cho Admin. Bạn không thể chỉnh sửa hay gửi lại.
                                        </p>
                                    </div>
                                    @endif
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
