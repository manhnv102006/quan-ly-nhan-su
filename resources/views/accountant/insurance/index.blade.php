@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

@include('accountant.insurance.partials.sub-nav', ['active' => 'profiles'])

<x-accountant-layout title="Bảo hiểm" subtitle="Hồ sơ tham gia BHXH · BHYT · BHTN">
    <div class="accountant-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Hồ sơ bảo hiểm</h2>
                <p class="text-sm text-slate-500">Quản lý tham gia, mức đóng và ngừng đóng khi nghỉ việc</p>
            </div>
            <a href="{{ route('accountant.insurance.create') }}" class="accountant-btn-primary">+ Thêm hồ sơ BH</a>
        </div>

        @if($resignedAlerts->isNotEmpty())
            <div class="rounded-2xl border border-rose-200 bg-rose-50/80 p-4">
                <p class="text-sm font-bold text-rose-800">Cảnh báo: {{ $resignedAlerts->count() }} nhân viên đã nghỉ việc nhưng vẫn đang đóng BH</p>
                <ul class="mt-3 space-y-2">
                    @foreach($resignedAlerts as $emp)
                        <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-white px-4 py-2 text-sm">
                            <span>
                                <strong>{{ $emp->full_name }}</strong>
                                <span class="text-slate-500">· {{ $emp->employee_code }}</span>
                            </span>
                            <form method="POST" action="{{ route('accountant.insurance.stop-resigned', $emp) }}">
                                @csrf
                                <button type="submit" class="accountant-btn-secondary !py-1 !text-xs text-rose-700">Ngừng đóng BH</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('accountant.partials.stat-card', ['label' => 'Đang đóng', 'value' => $stats['active'], 'tone' => 'text-emerald-600'])
            @include('accountant.partials.stat-card', ['label' => 'Tạm dừng', 'value' => $stats['suspended'], 'tone' => 'text-amber-600'])
            @include('accountant.partials.stat-card', ['label' => 'Đã ngừng', 'value' => $stats['stopped']])
            @include('accountant.partials.stat-card', ['label' => 'Chưa có hồ sơ', 'value' => $stats['no_profile'], 'tone' => 'text-rose-600'])
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[180px] flex-1">
                <label class="accountant-label">Tìm nhân viên</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tên hoặc mã NV..." class="accountant-field">
            </div>
            <div class="min-w-[160px]">
                <label class="accountant-label">Trạng thái</label>
                <select name="status" class="accountant-field">
                    <option value="">Tất cả</option>
                    @foreach(\App\Models\EmployeeInsurance::STATUS_LABELS as $val => $label)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="accountant-btn-primary">Lọc</button>
        </form>

        <div class="accountant-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-sky-50 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-5 py-3">Nhân viên</th>
                            <th class="px-5 py-3">Số BHXH</th>
                            <th class="px-5 py-3 text-right">Lương đóng BH</th>
                            <th class="px-5 py-3 text-right">NLĐ đóng</th>
                            <th class="px-5 py-3 text-right">DN đóng</th>
                            <th class="px-5 py-3">Thời hạn</th>
                            <th class="px-5 py-3 text-center">TT</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($profiles as $profile)
                            @php $c = app(\App\Services\InsuranceService::class)->calculateContributions($profile); @endphp
                            <tr class="hover:bg-sky-50/30">
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-800">{{ $profile->employee?->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $profile->employee?->employee_code }} · {{ $profile->employee?->department?->department_name }}</p>
                                    @if($profile->employee?->status === 'resigned' && $profile->status === 'active')
                                        <span class="mt-1 inline-block text-xs font-bold text-rose-600">NV đã nghỉ việc</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">{{ $profile->social_insurance_number ?: '—' }}</td>
                                <td class="px-5 py-3 text-right font-medium">{{ $formatMoney($profile->contribution_salary) }}</td>
                                <td class="px-5 py-3 text-right text-sky-700">{{ $formatMoney($c['total_employee']) }}</td>
                                <td class="px-5 py-3 text-right text-indigo-700">{{ $formatMoney($c['total_employer']) }}</td>
                                <td class="px-5 py-3 text-xs">
                                    {{ $profile->start_date?->format('d/m/Y') }}
                                    @if($profile->end_date) → {{ $profile->end_date->format('d/m/Y') }} @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="accountant-badge {{ $profile->statusBadgeClass() }}">{{ $profile->statusLabel() }}</span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('accountant.insurance.edit', $profile) }}" class="accountant-btn-secondary !py-1 !text-xs">Sửa</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-12 text-center text-slate-500">Chưa có hồ sơ bảo hiểm.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($profiles->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $profiles->links() }}</div>
            @endif
        </div>
    </div>
</x-accountant-layout>
