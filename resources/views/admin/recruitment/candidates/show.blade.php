<x-admin-layout title="Chi tiết ứng viên">
    @php
        $statusLabels = [
            'new' => 'Mới',
            'interview' => 'Phỏng vấn',
            'passed' => 'Đạt',
            'failed' => 'Không đạt',
        ];
        $statusClasses = [
            'new' => 'bg-sky-100 text-sky-700 ring-sky-200',
            'interview' => 'bg-amber-100 text-amber-700 ring-amber-200',
            'passed' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'failed' => 'bg-rose-100 text-rose-700 ring-rose-200',
        ];
        $statusClass = $statusClasses[$candidate->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
        $parts = collect(preg_split('/\s+/', trim($candidate->full_name)))->filter();
        $initial = $parts->isNotEmpty() ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $parts->last(), 0, 1)) : 'UV';
        $fieldClass = 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10';
    @endphp

    @include('admin.recruitment.partials.ui-contrast')

    <div class="recruitment-ui max-w-full overflow-hidden space-y-6">
        <section class="recruitment-hero rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600">Tuyển dụng</a>
                        <span>/</span>
                        <a href="{{ route('admin.recruitment.candidates') }}" class="hover:text-cyan-600">Ứng viên</a>
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
                                Đã chuyển nhân viên
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    @if ($cvUrl)
                        <a href="{{ $cvUrl }}" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                            Mở CV
                        </a>
                    @endif
                    <a href="{{ route('admin.recruitment.candidates') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700">
                        Quay lại
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

        @if (isset($errors) && $errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <p class="font-bold">Vui lòng kiểm tra lại thông tin:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-8">
                <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                        <h3 class="text-base font-black text-slate-900">Thông tin ứng viên</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Email</p>
                            <p class="mt-2 break-words text-sm font-semibold text-slate-800">{{ $candidate->email }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Số điện thoại</p>
                            <p class="mt-2 break-words text-sm font-semibold text-slate-800">{{ $candidate->phone }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Ngày sinh</p>
                            <p class="mt-2 text-sm font-semibold text-slate-800">{{ $candidate->birth_date?->format('d/m/Y') ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Ngày tạo hồ sơ</p>
                            <p class="mt-2 text-sm font-semibold text-slate-800">{{ $candidate->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4 sm:col-span-2">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Địa chỉ</p>
                            <p class="mt-2 break-words text-sm font-semibold leading-6 text-slate-800">{{ $candidate->address }}</p>
                        </div>
                    </div>
                </section>

                <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                        <h3 class="text-base font-black text-slate-900">Tin tuyển dụng liên kết</h3>
                    </div>
                    <div class="p-5 sm:p-6">
                        @if ($candidate->jobPost)
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl bg-slate-50 p-4 sm:col-span-2">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Vị trí</p>
                                    <p class="mt-2 break-words text-sm font-bold text-slate-900">{{ $candidate->jobPost->title }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Phòng ban</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-800">{{ $candidate->jobPost->department?->department_name ?? 'Chưa gắn' }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Trạng thái tin</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-800">{{ $candidate->jobPost->status === 'open' ? 'Đang mở' : 'Đã đóng' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-500">
                                Ứng viên chưa được gắn với tin tuyển dụng nào.
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <aside class="space-y-6 xl:col-span-4">
                <section class="recruitment-panel rounded-[2rem] border border-slate-100 bg-white p-6 text-center shadow-sm shadow-slate-200/60">
                    <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[1.75rem] bg-cyan-50 text-3xl font-black text-cyan-700">
                        {{ $initial }}
                    </div>
                    <h3 class="mt-5 break-words text-xl font-black text-slate-900">{{ $candidate->full_name }}</h3>
                    <p class="mt-2 break-words text-sm text-slate-500">{{ $candidate->email }}</p>
                    @if ($candidate->employee)
                        <a href="{{ route('admin.employees.show', $candidate->employee) }}"
                           class="mt-5 inline-flex rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700">
                            Xem nhân viên
                        </a>
                    @endif
                </section>

                <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4">
                        <h3 class="text-base font-black text-slate-900">Tình trạng CV</h3>
                    </div>
                    <div class="space-y-4 p-5">
                        @if ($cvUrl)
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                                <p class="text-sm font-bold text-emerald-800">CV đã sẵn sàng</p>
                                <p class="mt-1 break-all text-xs leading-5 text-emerald-700">{{ $candidate->cv_file }}</p>
                            </div>
                            <a href="{{ $cvUrl }}" target="_blank" rel="noopener"
                               class="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-700">
                                Mở CV
                            </a>
                        @elseif ($candidate->cv_file)
                            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                                <p class="text-sm font-bold text-amber-800">Đường dẫn CV không khả dụng</p>
                                <p class="mt-1 break-all text-xs leading-5 text-amber-700">{{ $candidate->cv_file }}</p>
                            </div>
                        @else
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-500">
                                Chưa có CV trong hệ thống.
                            </div>
                        @endif
                    </div>
                </section>

                <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-violet-100 bg-white shadow-sm shadow-violet-100/60">
                    <div class="border-b border-violet-100 bg-violet-50 px-5 py-4">
                        <h3 class="text-base font-black text-violet-950">Tạo lịch phỏng vấn</h3>
                        <p class="mt-1 text-sm leading-6 text-violet-800">
                            Lên lịch phỏng vấn cho ứng viên này. Người phỏng vấn là quản lý phòng ban của tin tuyển dụng.
                        </p>
                    </div>

                    @if ($canScheduleInterview)
                        <form action="{{ route('admin.recruitment.interviews.store') }}" method="POST" class="space-y-4 p-5">
                            @csrf
                            <input type="hidden" name="candidate_id" value="{{ $candidate->id }}">
                            <input type="hidden" name="return_to" value="{{ route('admin.recruitment.candidates.show', $candidate) }}">

                            @php
                                $departmentManager = $candidate->jobPost?->department?->manager;
                            @endphp
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Người phỏng vấn</label>
                                <div class="{{ $fieldClass }} bg-slate-50 text-slate-700">
                                    @if ($departmentManager)
                                        {{ $departmentManager->employee_code ? $departmentManager->employee_code.' - ' : '' }}{{ $departmentManager->full_name }}
                                    @elseif ($candidate->jobPost?->department)
                                        Phòng ban chưa có quản lý
                                    @else
                                        Ứng viên chưa gắn tin/phòng ban
                                    @endif
                                </div>
                                <p class="mt-2 text-xs text-slate-500">Tự động theo quản lý phòng ban.</p>
                            </div>

                            <div>
                                <label for="interview_date" class="mb-2 block text-sm font-bold text-slate-700">Thời gian phỏng vấn <span class="text-red-500">*</span></label>
                                <input type="datetime-local" id="interview_date" name="interview_date"
                                       value="{{ old('interview_date') }}"
                                       min="{{ now()->format('Y-m-d\TH:i') }}"
                                       required
                                       class="{{ $fieldClass }} @error('interview_date') border-red-400 @enderror">
                                @error('interview_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="interview_note" class="mb-2 block text-sm font-bold text-slate-700">Ghi chú</label>
                                <textarea id="interview_note" name="note" rows="3" placeholder="Địa điểm, link meet, yêu cầu chuẩn bị…"
                                          class="{{ $fieldClass }} resize-y @error('note') border-red-400 @enderror">{{ old('note') }}</textarea>
                                @error('note')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            @error('candidate_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

                            <button type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-violet-700">
                                Tạo lịch phỏng vấn
                            </button>
                        </form>
                    @else
                        <div class="space-y-4 p-5">
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-600">
                                Ứng viên đã có lịch phỏng vấn. Xem tại trang quản lý phỏng vấn.
                            </div>
                            <a href="{{ route('admin.recruitment.interviews') }}"
                               class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-violet-300 hover:text-violet-800">
                                Mở danh sách phỏng vấn
                            </a>
                        </div>
                    @endif
                </section>

                @if ($candidate->status === 'passed' && $candidate->employee_id === null)
                    <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-emerald-100 bg-white shadow-sm shadow-emerald-100/60">
                        <div class="border-b border-emerald-100 bg-emerald-50 px-5 py-4">
                            <h3 class="text-base font-black text-emerald-950">Chuyển thành nhân viên</h3>
                            <p class="mt-1 text-sm text-emerald-700">Chỉ áp dụng cho ứng viên đã đạt.</p>
                        </div>
                        <form action="{{ route('admin.recruitment.candidates.convert-to-employee', $candidate) }}" method="POST" class="space-y-4 p-5">
                            @csrf

                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Mã nhân viên</label>
                                <input type="text" name="employee_code" value="{{ old('employee_code', $suggestedEmployeeCode) }}" class="{{ $fieldClass }}">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Giới tính</label>
                                <select name="gender" class="{{ $fieldClass }}">
                                    <option value="male" @selected(old('gender') === 'male')>Nam</option>
                                    <option value="female" @selected(old('gender') === 'female')>Nữ</option>
                                    <option value="other" @selected(old('gender') === 'other')>Khác</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Ngày sinh</label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $candidate->birth_date?->format('Y-m-d')) }}" class="{{ $fieldClass }}">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Ngày vào làm</label>
                                <input type="date" name="hire_date" value="{{ old('hire_date', now()->format('Y-m-d')) }}" class="{{ $fieldClass }}">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Phòng ban</label>
                                @include('admin.partials.department-select', [
                                    'departments' => $departments,
                                    'selected' => old('department_id'),
                                    'required' => false,
                                    'placeholder' => 'Chưa gắn',
                                ])
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">Chức vụ</label>
                                <select name="position_id" class="{{ $fieldClass }}">
                                    <option value="">Chưa gắn</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>{{ $position->position_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700">
                                Tạo hồ sơ nhân viên
                            </button>
                        </form>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</x-admin-layout>
