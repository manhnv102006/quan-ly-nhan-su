@php
    $navigation = \App\Support\ManagerNavigation::items();
    $statusClasses = [
        'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'rejected' => 'bg-rose-50 text-rose-700 border-rose-100',
    ];
    $statusLabels = [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];
@endphp

<x-staff-layout
    title="Chi tiết đơn nghỉ phép"
    subtitle="Nhân viên: {{ $leaveRequest->employee?->full_name ?? '—' }}"
    role="manager"
    :navigation="$navigation"
>
    <div class="space-y-6">
        @if (session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-sm">
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 shadow-sm">
                <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4">
                <ul class="list-disc space-y-1 ps-5 text-sm text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-600">Chi tiết yêu cầu</p>
                <h2 class="mt-1 text-xl font-bold text-slate-800">Đơn nghỉ phép #{{ $leaveRequest->id }}</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('manager.leave-requests.index') }}"
                   class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    ← Quay lại
                </a>
                @can('approve', $leaveRequest)
                    @if($leaveRequest->isPending())
                        <form method="POST" action="{{ route('manager.leave-requests.approve', $leaveRequest) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" onclick="return confirm('Duyệt đơn này?')"
                                    class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-500/20 transition hover:bg-emerald-700">
                                Duyệt đơn
                            </button>
                        </form>
                    @endif
                @endcan
                @can('reject', $leaveRequest)
                    @if($leaveRequest->isPending())
                        <button type="button"
                                x-data
                                x-on:click="$dispatch('open-modal', 'reject-leave')"
                                class="inline-flex items-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-500/20 transition hover:bg-rose-700">
                            Từ chối
                        </button>
                    @endif
                @endcan
            </div>
        </div>

        <section class="staff-card p-6 sm:p-7">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Nhân viên</p>
                    <p class="mt-2 text-base font-bold text-slate-800">{{ $leaveRequest->employee?->full_name ?? '—' }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $leaveRequest->employee?->employee_code ?? '—' }}</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Phòng ban</p>
                    <p class="mt-2 text-base font-bold text-slate-800">{{ $leaveRequest->employee?->department?->department_name ?? '—' }}</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Chức vụ</p>
                    <p class="mt-2 text-base font-bold text-slate-800">{{ $leaveRequest->employee?->position?->position_name ?? '—' }}</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Loại nghỉ</p>
                    <p class="mt-2 text-base font-bold text-slate-800">{{ \App\Models\LeaveRequest::LEAVE_TYPE_LABELS[$leaveRequest->leave_type] ?? $leaveRequest->leave_type }}</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Thời gian nghỉ</p>
                    <p class="mt-2 text-base font-bold text-slate-800">
                        {{ optional($leaveRequest->start_date)->format('d/m/Y') }} → {{ optional($leaveRequest->end_date)->format('d/m/Y') }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">{{ $leaveRequest->total_days }} ngày</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Trạng thái</p>
                    <p class="mt-3">
                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-bold {{ $statusClasses[$leaveRequest->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                            {{ $statusLabels[$leaveRequest->status] ?? $leaveRequest->status }}
                        </span>
                    </p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4 sm:col-span-2 lg:col-span-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Lý do nghỉ</p>
                    <p class="mt-2 text-sm leading-6 text-slate-700">{{ $leaveRequest->reason ?? '—' }}</p>
                </div>
            </div>
        </section>

        @if(in_array($leaveRequest->status, [\App\Models\LeaveRequest::STATUS_APPROVED, \App\Models\LeaveRequest::STATUS_REJECTED], true))
            <section class="staff-card p-6 sm:p-7">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-600">Phê duyệt</p>
                <h3 class="mt-2 text-lg font-bold text-slate-800">Thông tin xử lý</h3>
                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @include('leave-requests.partials.approval-info', ['leaveRequest' => $leaveRequest, 'variant' => 'tailwind'])
                </div>
            </section>
        @endif

        @include('manager.leave-requests.partials.history-table', ['leaveRequest' => $leaveRequest])
    </div>

    @can('reject', $leaveRequest)
        @if($leaveRequest->isPending())
            <x-modal name="reject-leave" :show="$errors->has('reject_reason')">
                <form method="POST" action="{{ route('manager.leave-requests.reject', $leaveRequest) }}" class="p-6">
                    @csrf
                    @method('PATCH')
                    <h3 class="text-lg font-bold text-slate-800">Từ chối đơn nghỉ phép</h3>
                    <p class="mt-1 text-sm text-slate-500">Vui lòng ghi rõ lý do để nhân viên nắm được thông tin.</p>

                    <div class="mt-5">
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                            Lý do từ chối <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="reject_reason" rows="4" required minlength="1"
                                  class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-rose-300 focus:ring-2 focus:ring-rose-500/20 @error('reject_reason') border-rose-400 @enderror">{{ old('reject_reason') }}</textarea>
                        @error('reject_reason')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button"
                                x-on:click="$dispatch('close-modal', 'reject-leave')"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Hủy
                        </button>
                        <button type="submit"
                                class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                            Xác nhận từ chối
                        </button>
                    </div>
                </form>
            </x-modal>
        @endif
    @endcan
</x-staff-layout>
