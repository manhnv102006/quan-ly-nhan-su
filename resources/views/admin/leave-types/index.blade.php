<x-admin-layout title="Quản lý loại nghỉ phép">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Danh mục loại nghỉ phép</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Cấu hình từng loại nghỉ: có trừ phép năm không, lương do ai trả, hạn mức và giấy tờ bắt buộc.
                    Nhân viên chỉ chọn được các loại đang bật.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.leave-requests') }}" class="admin-btn-secondary">Đơn nghỉ phép</a>
                <a href="{{ route('admin.leave-types.create') }}" class="admin-btn-violet">+ Thêm loại nghỉ phép</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="admin-card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <form method="GET" class="flex flex-wrap gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="admin-field max-w-xs"
                           placeholder="Tìm tên hoặc mã...">
                    <select name="status" class="admin-field max-w-[180px]">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" @selected(request('status') === 'active')>Đang dùng</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Đã tắt</option>
                    </select>
                    <button type="submit" class="admin-btn-secondary">Lọc</button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.leave-types.index') }}" class="admin-btn-secondary">Xóa lọc</a>
                    @endif
                </form>
                <p class="text-xs font-semibold text-slate-500">
                    {{ $activeCount }}/{{ $totalCount }} loại đang được dùng
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1080px]">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-5 py-3">Loại nghỉ phép</th>
                            <th class="px-5 py-3">Trừ phép năm</th>
                            <th class="px-5 py-3">Lương do ai trả</th>
                            <th class="px-5 py-3">Hạn mức</th>
                            <th class="px-5 py-3 text-center">Giấy tờ</th>
                            <th class="px-5 py-3 text-center">Trạng thái</th>
                            <th class="px-5 py-3 text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($leaveTypes as $type)
                            <tr class="align-top hover:bg-slate-50/60">
                                <td class="px-5 py-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $type->badgeClass() }}">
                                            {{ $type->name }}
                                        </span>
                                        @if ($type->is_system)
                                            <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500">Hệ thống</span>
                                        @endif
                                        @if ($type->gender_restriction)
                                            <span class="rounded bg-violet-50 px-1.5 py-0.5 text-[10px] font-bold text-violet-600">
                                                {{ \App\Models\LeaveType::GENDER_LABELS[$type->gender_restriction] }}
                                            </span>
                                        @endif
                                        @if ($type->auto_generated)
                                            <span class="rounded bg-sky-50 px-1.5 py-0.5 text-[10px] font-bold text-sky-600">Tự sinh</span>
                                        @endif
                                        @if (! $type->counts_as_leave)
                                            <span class="rounded bg-teal-50 px-1.5 py-0.5 text-[10px] font-bold text-teal-600">Không tính nghỉ</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 font-mono text-[11px] text-slate-400">{{ $type->code }}</p>
                                    <p class="mt-0.5 text-[11px] text-slate-500">{{ $type->leave_requests_count }} đơn đã dùng</p>
                                </td>
                                <td class="px-5 py-3 text-sm text-slate-600">{{ $type->annualDeductionLabel() }}</td>
                                <td class="px-5 py-3 text-sm text-slate-600">{{ $type->salaryPayerLabel() }}</td>
                                <td class="px-5 py-3 text-sm text-slate-600">
                                    {{ $type->quotaLabel() }}
                                    @if ($type->quotaIsEnforced())
                                        <span class="mt-1 block text-[11px] font-semibold text-rose-600">Chặn khi vượt hạn mức</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if ($type->requiresDocument())
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700">Bắt buộc</span>
                                        <p class="mt-1 text-[11px] text-slate-500">{{ $type->document_hint }}</p>
                                    @else
                                        <span class="text-sm text-slate-400">Không</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $type->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $type->is_active ? 'Đang dùng' : 'Đã tắt' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center whitespace-nowrap">
                                    <a href="{{ route('admin.leave-types.edit', $type) }}"
                                       class="text-sm font-semibold text-violet-600 hover:text-violet-700">Sửa</a>
                                    @if (! $type->is_system && $type->leave_requests_count === 0)
                                        <form action="{{ route('admin.leave-types.destroy', $type) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Xóa loại nghỉ phép {{ $type->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-2 text-sm font-semibold text-rose-600 hover:text-rose-700">Xóa</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Không có loại nghỉ phép nào khớp bộ lọc.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($leaveTypes->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $leaveTypes->links() }}</div>
            @endif
        </div>

        <div class="rounded-2xl border border-sky-200 bg-sky-50/80 px-5 py-4 text-xs leading-relaxed text-sky-900">
            <p class="text-sm font-bold text-sky-950">Lưu ý khi cấu hình</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                <li>Loại <strong>hệ thống</strong> không xóa được — hãy <strong>tắt</strong> để ngừng cho nhân viên chọn, lịch sử đơn cũ vẫn giữ nguyên tên loại.</li>
                <li><strong>Phép năm</strong> và <strong>Nghỉ không lương</strong> luôn phải bật: phần phép năm vượt số dư được hệ thống tự tách sang nghỉ không lương.</li>
                <li>Loại đánh dấu <strong>tự sinh</strong> (ngày lễ, Tết) không hiện trên form của nhân viên — hệ thống/HR ghi nhận theo lịch nghỉ.</li>
                <li>Chỉ hạn mức dạng <strong>số ngày/năm</strong> hoặc <strong>số ngày/lần nghỉ</strong> mới chặn được tự động khi nhân viên gửi đơn.</li>
                <li>Hạn mức theo BHXH thay đổi theo từng người (thâm niên đóng, tuổi con, tuổi thai) nên chỉ nên ghi chú, không bật chặn tự động.</li>
            </ul>
        </div>
    </div>
</x-admin-layout>
