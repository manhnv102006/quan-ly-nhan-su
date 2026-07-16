<x-leader-layout title="Đề xuất thành viên" subtitle="Chờ Manager duyệt">
    <div class="leader-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Đề xuất thêm/bớt thành viên</h2>
                <p class="text-sm text-slate-500">Đề xuất của bạn sẽ chờ Quản lý phòng ban phê duyệt trước khi có hiệu lực.</p>
            </div>
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

        <div class="leader-card p-5" x-data="{ action: '{{ old('action', 'add') }}' }">
            <h3 class="mb-4 font-bold text-slate-900">Tạo đề xuất mới</h3>
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
                    <select name="employee_id" class="leader-field">
                        <optgroup label="Có thể thêm (cùng phòng ban, chưa có nhóm)" x-show="action === 'add'">
                            @foreach ($addCandidates as $candidate)
                                <option value="{{ $candidate->id }}" @selected(old('employee_id') == $candidate->id)>{{ $candidate->full_name }} ({{ $candidate->employee_code }})</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Thành viên hiện tại (có thể đưa ra khỏi nhóm)" x-show="action === 'remove'">
                            @foreach ($removeCandidates as $candidate)
                                <option value="{{ $candidate->id }}" @selected(old('employee_id') == $candidate->id)>{{ $candidate->full_name }} ({{ $candidate->employee_code }})</option>
                            @endforeach
                        </optgroup>
                    </select>
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
                            <tr>
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
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400">Chưa có đề xuất nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $requests->links() }}</div>
        </div>
    </div>
</x-leader-layout>
