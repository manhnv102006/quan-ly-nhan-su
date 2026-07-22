<x-admin-layout title="Chi tiết tin tuyển dụng">
    @php
        $workTypes = [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'remote' => 'Remote',
            'hybrid' => 'Hybrid',
            'contract' => 'Hợp đồng',
        ];
        $statusLabels = [
            'open' => ['label' => 'Đang tuyển', 'class' => 'bg-emerald-100 text-emerald-800'],
            'closed' => ['label' => 'Đã đóng', 'class' => 'bg-slate-100 text-slate-700'],
            'pending_approval' => ['label' => 'Chờ duyệt', 'class' => 'bg-amber-100 text-amber-800'],
            'rejected' => ['label' => 'Đã từ chối', 'class' => 'bg-rose-100 text-rose-800'],
        ];
        $candidateStatus = [
            'new' => ['label' => 'Mới', 'class' => 'bg-sky-100 text-sky-800'],
            'interview' => ['label' => 'Phỏng vấn', 'class' => 'bg-violet-100 text-violet-800'],
            'passed' => ['label' => 'Đạt', 'class' => 'bg-emerald-100 text-emerald-800'],
            'failed' => ['label' => 'Không đạt', 'class' => 'bg-rose-100 text-rose-800'],
        ];
        $statusMeta = $statusLabels[$jobPost->status] ?? ['label' => $jobPost->status, 'class' => 'bg-slate-100 text-slate-700'];
    @endphp

    @include('admin.recruitment.partials.ui-contrast')

    <div class="recruitment-ui max-w-full space-y-6">
        <section class="recruitment-hero rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600">Tuyển dụng</a>
                        <span>/</span>
                        <a href="{{ route('admin.recruitment.job-posts') }}" class="hover:text-cyan-600">Tin tuyển dụng</a>
                        <span>/</span>
                        <span class="font-semibold text-slate-700">Chi tiết</span>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <h2 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">{{ $jobPost->title }}</h2>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">
                        {{ $jobPost->department?->department_name ?? 'Chưa gắn phòng ban' }}
                        @if ($jobPost->work_location)
                            · {{ $jobPost->work_location }}
                        @endif
                        @if ($jobPost->work_type)
                            · {{ $workTypes[$jobPost->work_type] ?? $jobPost->work_type }}
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap gap-2 shrink-0">
                    @if ($jobPost->status === 'open')
                        <a href="{{ route('public.recruitment.show', $jobPost) }}" target="_blank" rel="noopener"
                           class="rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-2.5 text-sm font-bold text-cyan-800 transition hover:bg-cyan-100">
                            Xem trang công khai
                        </a>
                    @endif
                    <a href="{{ route('admin.recruitment.job-posts.edit', $jobPost) }}"
                       class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-amber-600">
                        Sửa tin
                    </a>
                    <a href="{{ route('admin.recruitment.job-posts') }}"
                       class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-200">
                        ← Danh sách
                    </a>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900">Thông tin tuyển dụng</h3>
                    <dl class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Số lượng cần tuyển</dt>
                            <dd class="mt-1 text-lg font-bold text-slate-900">{{ $jobPost->quantity }}</dd>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Mức lương</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-900">
                                @if ($jobPost->salary_min || $jobPost->salary_max)
                                    {{ $jobPost->salary_min ? number_format((float) $jobPost->salary_min, 0, ',', '.') : '0' }}
                                    –
                                    {{ $jobPost->salary_max ? number_format((float) $jobPost->salary_max, 0, ',', '.') : 'Thỏa thuận' }} đ
                                @else
                                    Thỏa thuận
                                @endif
                            </dd>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Hạn nộp hồ sơ</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-900">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</dd>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Ngày tạo / cập nhật</dt>
                            <dd class="mt-1 text-sm text-slate-800">{{ $jobPost->created_at?->format('d/m/Y H:i') }} · {{ $jobPost->updated_at?->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Mô tả công việc</h3>
                        <div class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->description ?: 'Chưa có mô tả.' }}</div>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Yêu cầu ứng viên</h3>
                        <div class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->requirements ?: 'Chưa có yêu cầu.' }}</div>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Quyền lợi</h3>
                        <div class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->benefits ?: 'Chưa có quyền lợi.' }}</div>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-slate-100 bg-white shadow-sm overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Ứng viên theo tin</h3>
                            <p class="text-sm text-slate-500">Tối đa 15 hồ sơ mới nhất</p>
                        </div>
                        <a href="{{ route('admin.recruitment.candidates', ['job_post_id' => $jobPost->id]) }}"
                           class="text-sm font-bold text-cyan-700 hover:text-cyan-900">
                            Xem tất cả →
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">Ứng viên</th>
                                    <th class="px-5 py-3">Liên hệ</th>
                                    <th class="px-5 py-3">Trạng thái</th>
                                    <th class="px-5 py-3">Ngày nộp</th>
                                    <th class="px-5 py-3 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($recentCandidates as $candidate)
                                    @php $cStatus = $candidateStatus[$candidate->status] ?? ['label' => $candidate->status, 'class' => 'bg-slate-100 text-slate-700']; @endphp
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="px-5 py-3 font-semibold text-slate-900">{{ $candidate->full_name }}</td>
                                        <td class="px-5 py-3 text-slate-600">
                                            <p>{{ $candidate->email }}</p>
                                            <p class="text-xs text-slate-500">{{ $candidate->phone }}</p>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $cStatus['class'] }}">{{ $cStatus['label'] }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-slate-600">{{ $candidate->created_at?->format('d/m/Y H:i') }}</td>
                                        <td class="px-5 py-3 text-right">
                                            <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}" class="font-bold text-cyan-700 hover:text-cyan-900">Hồ sơ</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-10 text-center text-slate-500">Chưa có ứng viên nộp hồ sơ cho tin này.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-wide text-slate-400">Phụ trách</h3>
                    <p class="mt-3 text-lg font-bold text-slate-900">{{ $jobPost->recruiter?->full_name ?? 'Chưa gán' }}</p>
                    @if ($jobPost->recruiter?->employee_code)
                        <p class="text-sm text-slate-500">Mã NV: {{ $jobPost->recruiter->employee_code }}</p>
                    @endif
                    @if ($jobPost->submittedBy)
                        <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            <p class="font-bold">Tin gửi từ manager</p>
                            <p class="mt-1">{{ $jobPost->submittedBy->full_name }}</p>
                        </div>
                    @endif
                </section>

                <section class="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-wide text-slate-400">Thống kê hồ sơ</h3>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li class="flex justify-between"><span class="text-slate-500">Tổng ứng viên</span><span class="font-bold text-slate-900">{{ $jobPost->candidates_count }}</span></li>
                        <li class="flex justify-between"><span class="text-slate-500">Mới</span><span class="font-bold text-sky-700">{{ $jobPost->candidates_new_count }}</span></li>
                        <li class="flex justify-between"><span class="text-slate-500">Phỏng vấn</span><span class="font-bold text-violet-700">{{ $jobPost->candidates_interview_count }}</span></li>
                        <li class="flex justify-between"><span class="text-slate-500">Đạt</span><span class="font-bold text-emerald-700">{{ $jobPost->candidates_passed_count }}</span></li>
                    </ul>
                </section>

                @if ($jobPost->status === 'pending_approval')
                    <section class="rounded-[2rem] border border-amber-100 bg-amber-50 p-6">
                        <p class="text-sm font-bold text-amber-900">Tin đang chờ duyệt</p>
                        <div class="mt-4 flex flex-col gap-2">
                            <form action="{{ route('admin.recruitment.job-posts.approve', $jobPost) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full rounded-xl bg-emerald-600 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">Duyệt tin</button>
                            </form>
                            <form action="{{ route('admin.recruitment.job-posts.reject', $jobPost) }}" method="POST" onsubmit="return confirm('Từ chối tin tuyển dụng này?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full rounded-xl bg-white py-2.5 text-sm font-bold text-rose-700 ring-1 ring-rose-200 hover:bg-rose-50">Từ chối</button>
                            </form>
                        </div>
                    </section>
                @elseif (! in_array($jobPost->status, ['pending_approval', 'rejected'], true))
                    <section class="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
                        <h3 class="text-sm font-black uppercase tracking-wide text-slate-400">Trạng thái tin</h3>
                        <form action="{{ route('admin.recruitment.job-posts.update-status', $jobPost) }}" method="POST" class="mt-4">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="w-full rounded-xl border-slate-200 text-sm font-bold focus:border-cyan-500 focus:ring-cyan-500/20" onchange="this.form.submit()">
                                <option value="open" @selected($jobPost->status === 'open')>Đang tuyển</option>
                                <option value="closed" @selected($jobPost->status === 'closed')>Đã đóng</option>
                            </select>
                        </form>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</x-admin-layout>
