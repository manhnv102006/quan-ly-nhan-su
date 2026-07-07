@php
    /** @var array<int, array{title?: string, description?: string|null}> $existingTasks */
    $existingTasks = $existingTasks ?? [];
@endphp

{{-- Nhiệm vụ cần thực hiện của KPI (checklist công việc) --}}
<div class="md:col-span-2">
    <div class="flex items-center justify-between mb-2">
        <label class="block text-sm font-semibold text-slate-700">
            Nhiệm vụ cần thực hiện
        </label>
        <button type="button" id="kpi_add_task"
                class="inline-flex items-center gap-1 rounded-lg bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-100 transition">
            + Thêm nhiệm vụ
        </button>
    </div>
    <p class="text-xs text-slate-500 mb-3">
        Liệt kê các đầu việc / nhiệm vụ cụ thể mà người nhận KPI cần hoàn thành.
    </p>

    <div id="kpi_tasks_wrapper" class="space-y-3">
        @foreach ($existingTasks as $i => $task)
            <div class="kpi-task-row rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-1 space-y-2">
                        <input type="text" name="tasks[{{ $i }}][title]"
                               value="{{ $task['title'] ?? '' }}"
                               class="w-full rounded-lg border border-slate-300 text-sm focus:border-violet-500 focus:ring-violet-500"
                               placeholder="Tên nhiệm vụ (VD: Gọi 50 khách hàng mỗi tuần)">
                        <textarea name="tasks[{{ $i }}][description]" rows="2"
                                  class="w-full rounded-lg border border-slate-300 text-sm focus:border-violet-500 focus:ring-violet-500"
                                  placeholder="Mô tả chi tiết nhiệm vụ (tuỳ chọn)">{{ $task['description'] ?? '' }}</textarea>
                    </div>
                    <button type="button"
                            class="kpi-remove-task mt-1 rounded-lg bg-rose-50 p-2 text-rose-600 hover:bg-rose-100 transition"
                            title="Xoá nhiệm vụ">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <p id="kpi_tasks_empty" class="text-sm text-slate-400 mt-2 {{ count($existingTasks) ? 'hidden' : '' }}">
        Chưa có nhiệm vụ nào. Bấm "Thêm nhiệm vụ" để bổ sung.
    </p>

    @error('tasks')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('kpi_tasks_wrapper');
        const addBtn = document.getElementById('kpi_add_task');
        const emptyMsg = document.getElementById('kpi_tasks_empty');

        if (!wrapper || !addBtn) {
            return;
        }

        let index = wrapper.querySelectorAll('.kpi-task-row').length;

        function taskRowHtml(i) {
            return `
            <div class="kpi-task-row rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-1 space-y-2">
                        <input type="text" name="tasks[${i}][title]"
                               class="w-full rounded-lg border border-slate-300 text-sm focus:border-violet-500 focus:ring-violet-500"
                               placeholder="Tên nhiệm vụ (VD: Gọi 50 khách hàng mỗi tuần)">
                        <textarea name="tasks[${i}][description]" rows="2"
                                  class="w-full rounded-lg border border-slate-300 text-sm focus:border-violet-500 focus:ring-violet-500"
                                  placeholder="Mô tả chi tiết nhiệm vụ (tuỳ chọn)"></textarea>
                    </div>
                    <button type="button"
                            class="kpi-remove-task mt-1 rounded-lg bg-rose-50 p-2 text-rose-600 hover:bg-rose-100 transition"
                            title="Xoá nhiệm vụ">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>`;
        }

        function refreshEmpty() {
            const hasRows = wrapper.querySelectorAll('.kpi-task-row').length > 0;
            emptyMsg.classList.toggle('hidden', hasRows);
        }

        addBtn.addEventListener('click', function () {
            wrapper.insertAdjacentHTML('beforeend', taskRowHtml(index));
            index++;
            refreshEmpty();
        });

        wrapper.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.kpi-remove-task');
            if (!removeBtn) {
                return;
            }
            removeBtn.closest('.kpi-task-row').remove();
            refreshEmpty();
        });

        refreshEmpty();
    });
</script>
