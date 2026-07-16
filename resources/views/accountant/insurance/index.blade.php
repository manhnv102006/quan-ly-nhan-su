@php
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';
    $totalEmployees = $departments->sum('employees_count');
    $totalActive = $departments->sum('active_insurance_count');
    $totalProfiles = $departments->sum('insurance_profiles_count');
@endphp

<x-accountant-layout title="Bảo hiểm" subtitle="Phòng ban → Nhân viên → Hồ sơ BH">
    @include('accountant.insurance.partials.sub-nav', ['active' => 'profiles'])
    <div class="accountant-page">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Hồ sơ bảo hiểm</h2>
                <p class="text-sm text-slate-500">Quản lý tham gia BHXH · BHYT · BHTN theo phòng ban và nhân viên.</p>
            </div>
            <a href="{{ route('accountant.insurance.reports') }}" class="accountant-btn-secondary">Báo cáo nộp BH</a>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif

        @if($resignedAlerts->isNotEmpty())
            <div class="rounded-2xl border border-rose-200 bg-rose-50/80 p-4">
                <p class="text-sm font-bold text-rose-800">Cảnh báo: {{ $resignedAlerts->count() }} nhân viên đã nghỉ việc nhưng vẫn đang đóng BH</p>
                <ul class="mt-3 space-y-2">
                    @foreach($resignedAlerts as $emp)
                        <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-white px-4 py-2 text-sm">
                            <span>
                                <strong>{{ $emp->full_name }}</strong>
                                <span class="text-slate-500">· {{ $emp->employee_code }}</span>
                                @if($emp->department)
                                    <span class="text-slate-400">· {{ $emp->department->department_name }}</span>
                                @endif
                            </span>
                            <div class="flex gap-2">
                                @if($emp->insurance)
                                    <a href="{{ route('accountant.insurance.index', ['employee_id' => $emp->id]) }}" class="accountant-btn-secondary !py-1 !text-xs">Xem hồ sơ</a>
                                @endif
                                <form method="POST" action="{{ route('accountant.insurance.stop-resigned', $emp) }}">
                                    @csrf
                                    <button type="submit" class="accountant-btn-secondary !py-1 !text-xs text-rose-700">Ngừng đóng BH</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('accountant.partials.stat-card', ['label' => 'Phòng ban', 'value' => $departments->count()])
            @include('accountant.partials.stat-card', ['label' => 'Đang đóng BH', 'value' => $stats['active'], 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'Có hồ sơ BH', 'value' => $totalProfiles, 'tone' => 'text-sky-600'])
            @include('accountant.partials.stat-card', ['label' => 'Chưa có hồ sơ', 'value' => $stats['no_profile'], 'tone' => 'text-rose-600'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-sky-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Chọn phòng ban</h3>
                <p class="text-xs text-slate-500">{{ $departments->count() }} phòng ban · {{ $totalEmployees }} nhân viên · {{ $totalActive }} đang đóng BH</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($departments as $department)
                    <a href="{{ route('accountant.insurance.index', ['department_id' => $department->id]) }}"
                       class="group rounded-2xl border border-sky-100/80 bg-gradient-to-br from-sky-50/40 to-indigo-50/30 p-5 transition hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-lg hover:shadow-sky-100/60">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-base font-bold text-slate-800 group-hover:text-sky-800">
                                    {{ $department->department_name }}
                                </h4>
                                <p class="mt-1 text-xs text-slate-500">{{ $department->department_code }}</p>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold text-sky-700 shadow-sm">→</span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold text-slate-800">{{ $department->employees_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Nhân viên</p>
                            </div>
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold text-emerald-600">{{ $department->active_insurance_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Đang đóng</p>
                            </div>
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold {{ $department->no_insurance_count > 0 ? 'text-rose-600' : 'text-slate-600' }}">
                                    {{ $department->no_insurance_count }}
                                </p>
                                <p class="text-[10px] font-medium text-slate-500">Chưa có BH</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-slate-500">Chưa có phòng ban nào.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-accountant-layout>
