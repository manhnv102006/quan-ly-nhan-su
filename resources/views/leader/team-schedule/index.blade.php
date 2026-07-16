<x-leader-layout title="Lịch làm việc nhóm" subtitle="Chỉ xem">
    <div class="leader-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Lịch làm việc nhóm</h2>
                <p class="text-sm text-slate-500">Ca làm việc của thành viên báo cáo cho {{ $leader->full_name }} (chỉ xem)</p>
            </div>
        </div>

        <form method="GET" class="leader-card flex flex-wrap items-end gap-4 p-5">
            <div>
                <label class="leader-label">Tuần bắt đầu từ</label>
                <input type="date" name="week_start" value="{{ $weekStart->toDateString() }}" class="leader-field">
            </div>
            <button type="submit" class="leader-btn-primary">Xem tuần</button>
            <a href="{{ route('leader.team-schedule.index') }}" class="leader-btn-secondary">Tuần hiện tại</a>
        </form>

        <div class="leader-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-violet-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            @foreach ($days as $day)
                                <th class="px-4 py-3">
                                    {{ ucfirst($day->translatedFormat('l')) }}
                                    <div class="mt-0.5 font-normal normal-case text-slate-400">{{ $day->format('d/m') }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="align-top">
                            @foreach ($days as $day)
                                @php $dayShifts = $shifts->get($day->toDateString(), collect()); @endphp
                                <td class="px-4 py-3">
                                    @forelse ($dayShifts as $employeeShift)
                                        <div class="mb-2 rounded-lg border border-violet-100 bg-violet-50/60 px-2.5 py-1.5">
                                            <p class="font-semibold text-slate-800">{{ $employeeShift->employee?->full_name }}</p>
                                            <p class="text-xs text-slate-500">
                                                {{ $employeeShift->shift?->shift_name }}
                                                ({{ $employeeShift->shift?->start_time?->format('H:i') }} - {{ $employeeShift->shift?->end_time?->format('H:i') }})
                                            </p>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-300">Không có ca</p>
                                    @endforelse
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-leader-layout>
