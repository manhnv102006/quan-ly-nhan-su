<x-manager-layout title="Nhóm làm việc" subtitle="Tạo nhóm và gán thành viên trong phòng ban">
    <div class="manager-page space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="manager-kicker">Đội ngũ</p>
                <h2 class="manager-title">Nhóm làm việc</h2>
                <p class="manager-subtitle">Tạo nhóm, chỉ định Trưởng nhóm và gán thành viên</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('manager.employees.index') }}" class="manager-btn-secondary">← Nhân viên</a>
                @if($manager)
                    <a href="{{ route('manager.teams.create') }}" class="manager-btn-primary">+ Tạo nhóm</a>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @if (! $manager)
            <div class="manager-card border border-amber-100 bg-amber-50/90 p-5">
                <p class="text-sm text-amber-800">Tài khoản chưa liên kết hồ sơ nhân viên.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="manager-stat-card">
                    <p class="text-sm text-slate-500">Tổng nhóm</p>
                    <p class="mt-2 text-3xl font-bold text-slate-800">{{ $teamList->count() }}</p>
                </div>
                <div class="manager-stat-card border border-teal-100">
                    <p class="text-sm text-slate-500">Thành viên đã gán</p>
                    <p class="mt-2 text-3xl font-bold text-teal-600">{{ $teamList->sum('members_count') }}</p>
                </div>
                <div class="manager-stat-card border border-amber-100">
                    <p class="text-sm text-slate-500">Chưa thuộc nhóm</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600">{{ $unassignedCount }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                @forelse($teamList as $team)
                    <div class="manager-card p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">{{ $team->name }}</h3>
                                @if($team->description)
                                    <p class="mt-1 text-sm text-slate-500">{{ Str::limit($team->description, 100) }}</p>
                                @endif
                            </div>
                            <span class="manager-badge {{ $team->status_tailwind }}">{{ $team->status_label }}</span>
                        </div>
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Trưởng nhóm</dt>
                                <dd class="font-semibold text-teal-700">{{ $team->leader?->full_name ?? '— Chưa gán —' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-slate-400">Thành viên</dt>
                                <dd class="font-bold text-slate-800">{{ $team->members_count }}</dd>
                            </div>
                        </dl>
                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('manager.teams.show', $team) }}" class="manager-btn-primary !py-2 !text-xs">Quản lý</a>
                            <a href="{{ route('manager.teams.edit', $team) }}" class="manager-btn-secondary !py-2 !text-xs">Sửa</a>
                        </div>
                    </div>
                @empty
                    <div class="manager-card col-span-full p-10 text-center text-slate-500">
                        <p class="font-semibold">Chưa có nhóm nào</p>
                        <p class="mt-1 text-sm">Nhấn "Tạo nhóm" để bắt đầu phân chia đội ngũ.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</x-manager-layout>
