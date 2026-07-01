@php
    $navigation = \App\Support\ManagerNavigation::items();
@endphp

<x-staff-layout
    title="Giao KPI cho nhân viên"
    subtitle="Giao mục tiêu KPI cho nhân viên trong phòng ban."
    role="manager"
    :navigation="$navigation"
>
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Giao mục tiêu cho nhân viên
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    KPI: {{ $assignment->kpi_code }} — {{ $assignment->kpi_title }}
                </p>
            </div>

            <a href="{{ route('manager.kpis.show', $assignment) }}"
               class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-medium hover:bg-slate-200 transition">
                ← Quay lại
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Thông tin KPI gốc --}}
        <div class="bg-violet-50 rounded-2xl p-5 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500">Mục tiêu của Manager</span>
                <span class="font-semibold text-slate-800">{{ number_format($assignment->target) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Người giao</span>
                <span class="font-semibold text-slate-800">{{ $assignment->assignedBy->name ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Ngày bắt đầu</span>
                <span class="font-semibold text-slate-800">{{ $assignment->start_date->format('d/m/Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Ngày kết thúc</span>
                <span class="font-semibold text-slate-800">{{ $assignment->end_date->format('d/m/Y') }}</span>
            </div>
        </div>

        {{-- Form giao KPI --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
            <form action="{{ route('manager.kpis.store_assign', $assignment) }}" method="POST" class="space-y-6">
                @csrf

                {{-- Nhân viên --}}
                <div>
                    <label for="employee_id" class="block text-sm font-semibold text-slate-700 mb-2">
                        Chọn nhân viên <span class="text-red-500">*</span>
                    </label>
                    <select name="employee_id" id="employee_id" required
                        class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500">
                        <option value="">-- Chọn nhân viên trong phòng ban --</option>
                        @forelse($employeesInDepartment as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }} ({{ $employee->employee_code }})
                            </option>
                        @empty
                            <option disabled>Không có nhân viên nào trong phòng ban của bạn.</option>
                        @endforelse
                    </select>
                    @error('employee_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tên mục tiêu --}}
                <div>
                    <label for="target" class="block text-sm font-semibold text-slate-700 mb-2">
                        Tên mục tiêu <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="target" id="target" value="{{ old('target') }}" required
                        placeholder="Ví dụ: Hoàn thành CRUD User"
                        class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500">
                    @error('target')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mô tả công việc --}}
                <div>
                    <label for="comment" class="block text-sm font-semibold text-slate-700 mb-2">
                        Mô tả công việc
                    </label>
                    <textarea name="comment" id="comment" rows="3"
                        placeholder="Ví dụ: Hoàn thành CRUD User, Validate, Search."
                        class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500">{{ old('comment') }}</textarea>
                    @error('comment')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Hạn chót --}}
                <div>
                    <label for="deadline" class="block text-sm font-semibold text-slate-700 mb-2">
                        Hạn chót <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="deadline" id="deadline" value="{{ old('deadline') }}" required
                        min="{{ now()->format('Y-m-d') }}"
                        max="{{ $assignment->end_date->format('Y-m-d') }}"
                        class="w-full rounded-xl border border-slate-300 focus:border-violet-500 focus:ring-violet-500">
                    <p class="text-xs text-slate-500 mt-1">
                        Hạn chót phải từ hôm nay đến trước ngày kết thúc KPI ({{ $assignment->end_date->format('d/m/Y') }}).
                    </p>
                    @error('deadline')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('manager.kpis.show', $assignment) }}"
                       class="px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                        Hủy
                    </a>
                    <button type="submit"
                        class="px-6 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                        Giao mục tiêu
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-staff-layout>
