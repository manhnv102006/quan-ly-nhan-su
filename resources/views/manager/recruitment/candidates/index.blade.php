<x-manager-layout title="Ứng viên phòng ban" subtitle="Ứng viên nộp hồ sơ vào tin tuyển dụng thuộc phòng ban bạn quản lý.">
@php
    $statusConfig = [
        'new' => ['label' => 'Mới', 'badge' => 'bg-sky-100 text-sky-700'],
        'interview' => ['label' => 'Phỏng vấn', 'badge' => 'bg-amber-100 text-amber-700'],
        'pending_hire_approval' => ['label' => 'Chờ admin duyệt', 'badge' => 'bg-violet-100 text-violet-700'],
        'passed' => ['label' => 'Đạt', 'badge' => 'bg-emerald-100 text-emerald-700'],
        'failed' => ['label' => 'Không đạt', 'badge' => 'bg-rose-100 text-rose-700'],
    ];
@endphp

<div class="manager-page space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('manager.recruitment.index') }}" class="text-sm font-semibold text-teal-700 hover:underline">← Tuyển dụng</a>
            <h2 class="mt-2 text-2xl font-bold text-slate-900">Ứng viên phòng ban</h2>
            <p class="mt-1 text-sm text-slate-500">Xem hồ sơ và tạo lịch phỏng vấn cho ứng viên thuộc phòng ban của bạn.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ([
            ['Tổng', $stats['total'], ''],
            ['Mới', $stats['new'], 'new'],
            ['Phỏng vấn', $stats['interview'], 'interview'],
            ['Chờ admin', $stats['pending_hire_approval'], 'pending_hire_approval'],
            ['Đạt', $stats['passed'], 'passed'],
            ['Không đạt', $stats['failed'], 'failed'],
        ] as [$label, $value, $filter])
            <a href="{{ $filter ? route('manager.recruitment.candidates.index', ['status' => $filter]) : route('manager.recruitment.candidates.index') }}"
               class="manager-card p-4 transition hover:shadow-md">
                <p class="text-xs font-semibold uppercase text-slate-400">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $value }}</p>
            </a>
        @endforeach
    </div>

    <form method="GET" class="manager-card flex flex-wrap items-end gap-3 p-5">
        <div class="min-w-[200px] flex-1">
            <label class="mb-1 block text-xs font-medium text-slate-600">Tìm kiếm</label>
            <input type="text" name="search" value="{{ $search }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Tên, email, SĐT...">
        </div>
        <div class="min-w-[160px]">
            <label class="mb-1 block text-xs font-medium text-slate-600">Trạng thái</label>
            <select name="status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <option value="">Tất cả</option>
                @foreach ($statusConfig as $val => $cfg)
                    <option value="{{ $val }}" @selected($status === $val)>{{ $cfg['label'] }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Lọc</button>
    </form>

    <div class="manager-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-sm">
                <thead>
                    <tr class="bg-teal-50/80 text-left text-xs font-bold uppercase text-slate-500">
                        <th class="px-4 py-3">Ứng viên</th>
                        <th class="px-4 py-3">Tin tuyển dụng</th>
                        <th class="px-4 py-3">Phòng ban</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($candidates as $candidate)
                        @php $cfg = $statusConfig[$candidate->status] ?? ['label' => $candidate->status, 'badge' => 'bg-slate-100 text-slate-700']; @endphp
                        <tr class="hover:bg-teal-50/30">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $candidate->full_name }}</p>
                                <p class="text-xs text-slate-500">{{ $candidate->email }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $candidate->jobPost?->title ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $candidate->jobPost?->department?->department_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('manager.recruitment.candidates.show', $candidate) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Chi tiết</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-14 text-center text-slate-500">Chưa có ứng viên trong phòng ban.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($candidates->hasPages())
            <div class="border-t px-5 py-4">{{ $candidates->links() }}</div>
        @endif
    </div>
</div>
</x-manager-layout>
