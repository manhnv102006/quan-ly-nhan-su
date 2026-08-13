<x-manager-layout title="Chi tiết ứng viên" subtitle="{{ $candidate->full_name }}">
@php
    $statusLabels = \App\Models\Candidate::statusLabels();
    $statusClasses = [
        'new' => 'bg-sky-100 text-sky-700',
        'interview' => 'bg-amber-100 text-amber-700',
        'pending_hire_approval' => 'bg-violet-100 text-violet-700',
        'passed' => 'bg-emerald-100 text-emerald-700',
        'failed' => 'bg-rose-100 text-rose-700',
    ];
    $fieldClass = 'w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20';
    $interview = $candidate->interviews->first();
@endphp

<div class="manager-page space-y-6">
    <a href="{{ route('manager.recruitment.candidates.index') }}" class="text-sm font-semibold text-teal-700 hover:underline">← Danh sách ứng viên</a>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif

    <div class="manager-card p-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">{{ $candidate->full_name }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $candidate->email }} · {{ $candidate->phone }}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$candidate->status] ?? 'bg-slate-100 text-slate-700' }}">
                {{ $statusLabels[$candidate->status] ?? $candidate->status }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="manager-card space-y-4 p-5">
            <h3 class="text-sm font-bold text-slate-800">Thông tin ứng viên</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><p class="text-xs text-slate-400">Ngày sinh</p><p class="font-medium">{{ $candidate->birth_date?->format('d/m/Y') ?? '—' }}</p></div>
                <div><p class="text-xs text-slate-400">Ngày nộp</p><p class="font-medium">{{ $candidate->created_at?->format('d/m/Y H:i') }}</p></div>
                <div class="col-span-2"><p class="text-xs text-slate-400">Địa chỉ</p><p class="font-medium">{{ $candidate->address }}</p></div>
            </div>
            @if ($cvUrl)
                <a href="{{ $cvUrl }}" target="_blank" class="inline-flex rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Mở CV</a>
            @endif
        </div>

        <div class="manager-card space-y-4 p-5">
            <h3 class="text-sm font-bold text-slate-800">Tin tuyển dụng</h3>
            @if ($candidate->jobPost)
                <div class="space-y-2 text-sm">
                    <p class="font-semibold text-slate-900">{{ $candidate->jobPost->title }}</p>
                    <p class="text-slate-500">{{ $candidate->jobPost->department?->department_name ?? '—' }}</p>
                </div>
            @else
                <p class="text-sm text-slate-500">Chưa gắn tin tuyển dụng.</p>
            @endif
        </div>
    </div>

    @include('recruitment.partials.interview-readonly', ['interview' => $interview])

    @if ($canScheduleInterview)
        <div class="manager-card p-5">
            <h3 class="text-sm font-bold text-slate-900">Tạo lịch phỏng vấn</h3>
            <p class="mt-1 text-sm text-slate-500">Chọn bạn hoặc thành viên phòng ban phỏng vấn. Hệ thống gửi email mời ứng viên.</p>
            <form action="{{ route('manager.recruitment.candidates.interviews.store', $candidate) }}" method="POST" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Người phỏng vấn <span class="text-rose-500">*</span></label>
                    @if ($departmentInterviewers->isEmpty())
                        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                            Phòng ban chưa có nhân viên để phân công phỏng vấn.
                        </p>
                    @else
                        <select name="interviewer_id" required class="{{ $fieldClass }}">
                            @foreach ($departmentInterviewers as $employee)
                                <option value="{{ $employee->id }}" @selected((string) old('interviewer_id', $manager->id) === (string) $employee->id)>
                                    {{ $employee->full_name }}
                                    @if ($employee->employee_code)
                                        ({{ $employee->employee_code }})
                                    @endif
                                    @if ($employee->id === $manager->id)
                                        — Tôi
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('interviewer_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    @endif
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Thời gian phỏng vấn <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" name="interview_date" required min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('interview_date') }}" class="{{ $fieldClass }}">
                    @error('interview_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Ghi chú</label>
                    <textarea name="note" rows="3" class="{{ $fieldClass }} resize-y" placeholder="Địa điểm, link meet...">{{ old('note') }}</textarea>
                </div>
                @if ($departmentInterviewers->isNotEmpty())
                    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Tạo lịch phỏng vấn</button>
                @endif
            </form>
        </div>
    @elseif ($interview && $interview->result === 'pending')
        <div class="manager-card border-teal-100 bg-teal-50/40 p-5 text-sm text-teal-900">
            Sau khi phỏng vấn, cập nhật kết quả tại trang <a href="{{ route('manager.recruitment.index') }}" class="font-semibold underline">Tuyển dụng phòng ban</a>.
        </div>
    @elseif ($candidate->status === 'pending_hire_approval')
        <div class="manager-card border-violet-100 bg-violet-50/40 p-5 text-sm text-violet-900">
            Đã gửi kết quả phỏng vấn cho Admin duyệt. Admin sẽ quyết định tuyển dụng và tạo hồ sơ nhân viên.
        </div>
    @endif
</div>
</x-manager-layout>
