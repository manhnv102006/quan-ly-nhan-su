<x-leader-layout title="Lịch làm việc nhóm" subtitle="Chỉ xem">
    <div class="leader-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Lịch làm việc nhóm</h2>
                <p class="text-sm text-slate-500">
                    Ca làm việc của {{ $members->count() }} thành viên
                    · Tuần {{ $weekStart->format('d/m') }} – {{ $weekEnd->format('d/m/Y') }}
                    · Chỉ xem
                </p>
            </div>
            <a href="{{ route('leader.employees.index') }}" class="leader-btn-secondary">← Thành viên</a>
        </div>

        <form method="GET" class="leader-card flex flex-wrap items-end gap-4 p-5">
            <div>
                <label class="leader-label">Tuần bắt đầu từ</label>
                <input type="date" name="week_start" value="{{ $weekStart->toDateString() }}" class="leader-field">
            </div>
            <button type="submit" class="leader-btn-primary">Xem tuần</button>
            <a href="{{ route('leader.team-schedule.index') }}" class="leader-btn-secondary">Tuần hiện tại</a>
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('leader.partials.stat-card', ['label' => 'Thành viên', 'value' => $members->count()])
            @include('leader.partials.stat-card', ['label' => 'Ca trong tuần', 'value' => $totalShifts, 'tone' => 'text-violet-700'])
            @include('leader.partials.stat-card', ['label' => 'TB ca/NV', 'value' => $members->count() > 0 ? round($totalShifts / $members->count(), 1) : 0, 'note' => 'Trung bình', 'tone' => 'text-sky-600'])
        </div>

        <div class="leader-card overflow-hidden">
            @if($members->isEmpty())
                <div class="px-5 py-14 text-center">
                    <p class="text-slate-500">Chưa có thành viên trong nhóm.</p>
                    <a href="{{ route('leader.team-requests.index') }}" class="mt-2 inline-block text-sm font-semibold text-violet-700 hover:underline">
                        Đề xuất thêm thành viên →
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[960px] text-sm">
                        <thead>
                            <tr class="bg-violet-50/80 text-left text-xs font-bold uppercase text-slate-500">
                                <th class="sticky left-0 z-10 min-w-[180px] bg-violet-50/95 px-4 py-3">Nhân viên</th>
                                @foreach ($days as $day)
                                    <th class="min-w-[120px] px-3 py-3 text-center">
                                        {{ ucfirst($day->translatedFormat('l')) }}
                                        <div class="mt-0.5 font-normal normal-case text-slate-400">{{ $day->format('d/m') }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($members as $member)
                                @php $memberShifts = $shiftsByEmployee->get($member->id, collect()); @endphp
                                <tr class="align-top hover:bg-violet-50/20">
                                    <td class="sticky left-0 z-10 bg-white px-4 py-3">
                                        <p class="font-semibold text-slate-800">{{ $member->full_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $member->employee_code }}</p>
                                    </td>
                                    @foreach ($days as $day)
                                        @php $dayShifts = $memberShifts->get($day->toDateString(), collect()); @endphp
                                        <td class="px-3 py-3 text-center">
                                            @forelse($dayShifts as $shift)
                                                <div class="mb-1 inline-block rounded-lg border border-violet-100 bg-violet-50/70 px-2.5 py-2 text-left last:mb-0">
                                                    <p class="text-xs font-bold text-violet-800">{{ $shift->shift?->shift_name }}</p>
                                                    <p class="mt-0.5 text-[10px] text-slate-500">
                                                        {{ $shift->shift?->start_time?->format('H:i') }}
                                                        -
                                                        {{ $shift->shift?->end_time?->format('H:i') }}
                                                    </p>
                                                </div>
                                            @empty
                                                <span class="text-xs text-slate-300">—</span>
                                            @endforelse
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-leader-layout>
