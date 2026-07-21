<x-admin-layout title="Chi tiet ung vien">
    @php
        $statusLabels = [
            'new' => 'Moi',
            'interview' => 'Phong van',
            'passed' => 'Dat',
            'failed' => 'Khong dat',
        ];

        $statusClasses = [
            'new' => 'bg-sky-100 text-sky-700 ring-sky-200',
            'interview' => 'bg-amber-100 text-amber-700 ring-amber-200',
            'passed' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'failed' => 'bg-rose-100 text-rose-700 ring-rose-200',
        ];

        $interviewResultLabels = [
            'pending' => 'Cho ket qua',
            'passed' => 'Dat',
            'failed' => 'Khong dat',
        ];

        $recommendationLabels = [
            'hire' => 'Nen tuyen',
            'consider' => 'Can can nhac',
            'reject' => 'Tu choi',
        ];

        $statusClass = $statusClasses[$candidate->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
        $parts = collect(preg_split('/\s+/', trim($candidate->full_name)))->filter();
        $initial = $parts->isNotEmpty() ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $parts->last(), 0, 1)) : 'UV';
    @endphp

    @include('admin.recruitment.partials.ui-contrast')

    <div class="recruitment-ui max-w-full overflow-hidden space-y-6">
        <section class="recruitment-hero rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600">Tuyen dung</a>
                        <span>/</span>
                        <a href="{{ route('admin.recruitment.candidates') }}" class="hover:text-cyan-600">Ung vien</a>
                        <span>/</span>
                        <span class="break-words font-semibold text-slate-700">{{ $candidate->full_name }}</span>
                    </div>
                    <h1 class="mt-3 break-words text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">{{ $candidate->full_name }}</h1>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass }}">
                            {{ $statusLabels[$candidate->status] ?? $candidate->status }}
                        </span>
                        @if ($candidate->employee)
                            <span class="inline-flex rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700 ring-1 ring-indigo-200">
                                Da chuyen nhan vien
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    @if ($cvUrl)
                        <a href="{{ $cvUrl }}" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                            Mo CV
                        </a>
                    @endif
                    @if ($candidate->interviews->isNotEmpty())
                        <a href="{{ route('admin.recruitment.interviewed-candidates', ['search' => $candidate->email]) }}"
                           class="inline-flex items-center justify-center rounded-2xl bg-amber-100 px-5 py-3 text-sm font-bold text-amber-700 transition hover:bg-amber-200">
                            Xu ly sau phong van
                        </a>
                    @endif
                    <a href="{{ route('admin.recruitment.candidates') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700">
                        Quay lai
                    </a>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-8">
                <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                        <h2 class="text-base font-black text-slate-900">Thong tin ung vien</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Email</p>
                            <p class="mt-2 break-words text-sm font-semibold text-slate-800">{{ $candidate->email }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">So dien thoai</p>
                            <p class="mt-2 break-words text-sm font-semibold text-slate-800">{{ $candidate->phone }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Ngay sinh</p>
                            <p class="mt-2 text-sm font-semibold text-slate-800">{{ $candidate->birth_date?->format('d/m/Y') ?? 'Chua cap nhat' }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Ngay tao ho so</p>
                            <p class="mt-2 text-sm font-semibold text-slate-800">{{ $candidate->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4 sm:col-span-2">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Dia chi</p>
                            <p class="mt-2 break-words text-sm font-semibold leading-6 text-slate-800">{{ $candidate->address }}</p>
                        </div>
                    </div>
                </section>

                <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                        <h2 class="text-base font-black text-slate-900">Tin tuyen dung lien ket</h2>
                    </div>
                    <div class="p-5 sm:p-6">
                        @if ($candidate->jobPost)
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl bg-slate-50 p-4 sm:col-span-2">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Vi tri</p>
                                    <p class="mt-2 break-words text-sm font-bold text-slate-900">{{ $candidate->jobPost->title }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Phong ban</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-800">{{ $candidate->jobPost->department?->department_name ?? 'Chua gan' }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Trang thai tin</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-800">{{ $candidate->jobPost->status === 'open' ? 'Dang mo' : 'Da dong' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-500">
                                Ung vien chua duoc gan voi tin tuyen dung nao.
                            </div>
                        @endif
                    </div>
                </section>

                <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                        <h2 class="text-base font-black text-slate-900">Lich su phong van</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($candidate->interviews as $interview)
                            <div class="grid gap-3 p-5 sm:grid-cols-[1fr_auto] sm:items-center sm:px-6">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900">{{ $interview->interview_date?->format('d/m/Y H:i') ?? '-' }}</p>
                                    <p class="mt-1 break-words text-sm text-slate-500">{{ $interview->interviewer?->full_name ?? 'Chua gan nguoi phong van' }}</p>
                                    @if ($interview->note)
                                        <p class="mt-2 break-words text-sm leading-6 text-slate-600">{{ $interview->note }}</p>
                                    @endif
                                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-500 sm:grid-cols-4">
                                        <span class="rounded-xl bg-slate-50 px-3 py-2">Tong quan: <strong class="text-slate-800">{{ $interview->overall_score ?? '-' }}/10</strong></span>
                                        <span class="rounded-xl bg-slate-50 px-3 py-2">Ky thuat: <strong class="text-slate-800">{{ $interview->technical_score ?? '-' }}/10</strong></span>
                                        <span class="rounded-xl bg-slate-50 px-3 py-2">Thai do: <strong class="text-slate-800">{{ $interview->attitude_score ?? '-' }}/10</strong></span>
                                        <span class="rounded-xl bg-slate-50 px-3 py-2">De xuat: <strong class="text-slate-800">{{ $recommendationLabels[$interview->recommendation] ?? 'Chua co' }}</strong></span>
                                    </div>
                                    @if ($interview->strengths || $interview->weaknesses)
                                        <details class="mt-3 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm">
                                            <summary class="cursor-pointer font-bold text-slate-700">Xem danh gia chi tiet</summary>
                                            <div class="mt-3 space-y-2 leading-6 text-slate-600">
                                                <p><strong class="text-slate-800">Diem manh:</strong> {{ $interview->strengths ?: 'Chua ghi nhan.' }}</p>
                                                <p><strong class="text-slate-800">Can cai thien:</strong> {{ $interview->weaknesses ?: 'Chua ghi nhan.' }}</p>
                                            </div>
                                        </details>
                                    @endif
                                </div>
                                <span class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                    {{ $interviewResultLabels[$interview->result] ?? $interview->result }}
                                </span>
                            </div>
                        @empty
                            <div class="p-8 text-center text-sm text-slate-500">Chua co lich phong van.</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="space-y-6 xl:col-span-4">
                <section class="recruitment-panel rounded-[2rem] border border-slate-100 bg-white p-6 text-center shadow-sm shadow-slate-200/60">
                    <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[1.75rem] bg-cyan-50 text-3xl font-black text-cyan-700">
                        {{ $initial }}
                    </div>
                    <h2 class="mt-5 break-words text-xl font-black text-slate-900">{{ $candidate->full_name }}</h2>
                    <p class="mt-2 break-words text-sm text-slate-500">{{ $candidate->email }}</p>
                    @if ($candidate->employee)
                        <a href="{{ route('admin.employees.show', $candidate->employee) }}"
                           class="mt-5 inline-flex rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700">
                            Xem nhan vien
                        </a>
                    @endif
                </section>

                <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4">
                        <h2 class="text-base font-black text-slate-900">Tinh trang CV</h2>
                    </div>
                    <div class="space-y-4 p-5">
                        @if ($cvUrl)
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                                <p class="text-sm font-bold text-emerald-800">CV da san sang</p>
                                <p class="mt-1 break-all text-xs leading-5 text-emerald-700">{{ $candidate->cv_file }}</p>
                            </div>
                            <a href="{{ $cvUrl }}" target="_blank" rel="noopener"
                               class="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-700">
                                Mo CV
                            </a>
                        @elseif ($candidate->cv_file)
                            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                                <p class="text-sm font-bold text-amber-800">Duong dan CV khong kha dung</p>
                                <p class="mt-1 break-all text-xs leading-5 text-amber-700">{{ $candidate->cv_file }}</p>
                            </div>
                        @else
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-500">
                                Chua co CV trong he thong.
                            </div>
                        @endif
                    </div>
                </section>

                <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4">
                        <h2 class="text-base font-black text-slate-900">Lich su email</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($candidate->emailLogs as $log)
                            <div class="p-5">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $log->subject ?? 'Email tuyen dung' }}</p>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold {{ $log->status === 'sent' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $log->status === 'sent' ? 'Da gui' : 'Loi' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-xs text-slate-500">{{ $log->sent_at?->format('d/m/Y H:i') ?? $log->created_at?->format('d/m/Y H:i') }}</p>
                            </div>
                        @empty
                            <div class="p-5 text-sm text-slate-500">Chua co email nao duoc ghi nhan.</div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-admin-layout>
