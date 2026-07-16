<x-leader-layout title="Đề xuất thành viên" subtitle="Chờ Manager duyệt">
    <div class="leader-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Đề xuất thêm/bớt thành viên</h2>
                <p class="text-sm text-slate-500">Đề xuất của bạn sẽ chờ Quản lý phòng ban phê duyệt trước khi có hiệu lực.</p>
            </div>
            <a href="{{ route('leader.employees.index') }}" class="leader-btn-secondary">← Thành viên nhóm</a>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('leader.partials.stat-card', ['label' => 'Có thể thêm', 'value' => $addCandidates->count(), 'note' => 'Cùng phòng ban, chưa có nhóm', 'tone' => 'text-emerald-600'])
            @include('leader.partials.stat-card', ['label' => 'Thành viên hiện tại', 'value' => $removeCandidates->count(), 'note' => 'Có thể đề xuất đưa ra', 'tone' => 'text-violet-700'])
            @include('leader.partials.stat-card', ['label' => 'Chờ duyệt', 'value' => $pendingCount, 'note' => 'Đề xuất đang xử lý', 'tone' => 'text-amber-600'])
        </div>

        <div class="leader-card p-5" x-data="{ action: '{{ old('action', request('action', 'add')) }}' }">
            <h3 class="mb-1 font-bold text-slate-900">Tạo đề xuất mới</h3>
            <p class="mb-4 text-sm text-slate-500">Chọn loại đề xuất và nhân viên liên quan.</p>
            <form method="POST" action="{{ route('leader.team-requests.store') }}" class="grid gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="leader-label">Loại đề xuất</label>
                    <select name="action" class="leader-field" x-model="action">
                        <option value="add">Thêm vào nhóm</option>
                        <option value="remove">Đưa ra khỏi nhóm</option>
                    </select>
                </div>
                <div>
                    <label class="leader-label">Nhân viên</label>
                    <template x-if="action === 'add'">
                        <select name="employee_id" class="leader-field">
                            <option value="">— Chọn nhân viên —</option>
                            @forelse ($addCandidates as $candidate)
                                <option value="{{ $candidate->id }}" @selected(old('employee_id', request('employee_id')) == $candidate->id)>
                                    {{ $candidate->full_name }} ({{ $candidate->employee_code }})
                                </option>
                            @empty
                                <option value="" disabled>Không có nhân viên phù hợp để thêm</option>
                            @endforelse
                        </select>
                    </template>
                    <template x-if="action === 'remove'">
                        <select name="employee_id" class="leader-field">
                            <option value="">— Chọn thành viên —</option>
                            @forelse ($removeCandidates as $candidate)
                                <option value="{{ $candidate->id }}" @selected(old('employee_id', request('employee_id')) == $candidate->id)>
                                    {{ $candidate->full_name }} ({{ $candidate->employee_code }})
                                </option>
                            @empty
                                <option value="" disabled>Chưa có thành viên trong nhóm</option>
                            @endforelse
                        </select>
                    </template>
                </div>
                <div class="sm:col-span-2">
                    <label class="leader-label">Lý do</label>
                    <textarea name="reason" rows="2" class="leader-field" placeholder="Giải thích lý do đề xuất...">{{ old('reason') }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="leader-btn-primary">Gửi đề xuất</button>
                </div>
            </form>
        </div>

        <div class="leader-card overflow-hidden">
            <div class="border-b border-violet-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Lịch sử đề xuất</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-sm">
                    <thead>
                        <tr class="bg-violet-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3">Loại</th>
                            <th class="px-4 py-3">Lý do</th>
                            <th class="px-4 py-3">Trạng thái</th>
                            <th class="px-4 py-3">Ghi chú duyệt</th>
                            <th class="px-4 py-3">Ngày gửi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($requests as $req)
                            <tr class="hover:bg-violet-50/30">
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $req->employee?->full_name }}</td>
                                <td class="px-4 py-3">{{ $req->actionLabel() }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $req->reason ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $req->statusBadgeClass() }}">{{ $req->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ $req->decision_note ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-400">Chưa có đề xuất nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>
</x-leader-layout>
