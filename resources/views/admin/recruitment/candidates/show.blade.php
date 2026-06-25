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
        $statusClass = $statusClasses[$candidate->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
        $parts = collect(preg_split('/\s+/', trim($candidate->full_name)))->filter();
        $initial = $parts->isNotEmpty() ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $parts->last(), 0, 1)) : 'UV';
        $fieldClass = 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10';
    @endphp

    <div class="max-w-full overflow-hidden space-y-6">
        <section class="rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600">Tuyen dung</a>
                        <span>/</span>
                        <a href="{{ route('admin.recruitment.candidates') }}" class="hover:text-cyan-600">Ung vien</a>
                        <span>/</span>
                        <span class="break-words font-semibold text-slate-700">{{ $candidate->full_name }}</span>
                    </div>
                    <h2 class="mt-3 break-words text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">{{ $candidate->full_name }}</h2>
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
                    <a href="{{ route('admin.recruitment.candidates.edit', $candidate) }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-amber-100 px-5 py-3 text-sm font-bold text-amber-700 transition hover:bg-amber-200">
                        Sua ho so
                    </a>
                    <a href="{{ route('admin.recruitment.candidates') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700">
                        Quay lai
                    </a>
                    <form action="{{ route('admin.recruitment.candidates.destroy', $candidate) }}" method="POST"
                          onsubmit="return confirm('Ban co chac muon xoa ung vien nay?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-red-50 px-5 py-3 text-sm font-bold text-red-700 transition hover:bg-red-100">
                            Xoa
                        </button>
                    </form>
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

        @if (isset($errors) && $errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <p class="font-bold">Vui long kiem tra lai thong tin:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-8">
                <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                        <h3 class="text-base font-black text-slate-900">Thong tin ung vien</h3>
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

                <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                        <h3 class="text-base font-black text-slate-900">Tin tuyen dung lien ket</h3>
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

                <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                        <h3 class="text-base font-black text-slate-900">Lich su phong van</h3>
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
                <section class="rounded-[2rem] border border-slate-100 bg-white p-6 text-center shadow-sm shadow-slate-200/60">
                    <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[1.75rem] bg-cyan-50 text-3xl font-black text-cyan-700">
                        {{ $initial }}
                    </div>
                    <h3 class="mt-5 break-words text-xl font-black text-slate-900">{{ $candidate->full_name }}</h3>
                    <p class="mt-2 break-words text-sm text-slate-500">{{ $candidate->email }}</p>
                    @if ($candidate->employee)
                        <a href="{{ route('admin.employees.show', $candidate->employee) }}"
                           class="mt-5 inline-flex rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700">
                            Xem nhan vien
                        </a>
                    @endif
                </section>

                <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4">
                        <h3 class="text-base font-black text-slate-900">Tinh trang CV</h3>
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

                @if ($candidate->status === 'passed' && $candidate->employee_id === null)
                    <section class="overflow-hidden rounded-[2rem] border border-emerald-100 bg-white shadow-sm shadow-emerald-100/60">
                        <div class="border-b border-emerald-100 bg-emerald-50 px-5 py-4">
                            <h3 class="text-base font-black text-emerald-950">Chuyen thanh nhan vien</h3>
                            <p class="mt-1 text-sm text-emerald-700">Chi ap dung cho ung vien da dat.</p>
                        </div>
                        <form action="{{ route('admin.recruitment.candidates.convert-to-employee', $candidate) }}" method="POST" class="space-y-4 p-5">
                            @csrf

                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Ma nhan vien</label>
                                <input type="text" name="employee_code" value="{{ old('employee_code', $suggestedEmployeeCode) }}" class="{{ $fieldClass }}">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Gioi tinh</label>
                                <select name="gender" class="{{ $fieldClass }}">
                                    <option value="male" @selected(old('gender') === 'male')>Nam</option>
                                    <option value="female" @selected(old('gender') === 'female')>Nu</option>
                                    <option value="other" @selected(old('gender') === 'other')>Khac</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Ngay sinh</label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $candidate->birth_date?->format('Y-m-d')) }}" class="{{ $fieldClass }}">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Ngay vao lam</label>
                                <input type="date" name="hire_date" value="{{ old('hire_date', now()->format('Y-m-d')) }}" class="{{ $fieldClass }}">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Phong ban</label>
                                <select name="department_id" class="{{ $fieldClass }}">
                                    <option value="">Chua gan</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Chuc vu</label>
                                <select name="position_id" class="{{ $fieldClass }}">
                                    <option value="">Chua gan</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>{{ $position->position_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700">
                                Tao ho so nhan vien
                            </button>
                        </form>
                    </section>
                @endif

                <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4">
                        <h3 class="text-base font-black text-slate-900">Lich su email</h3>
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
