{{-- KPI details preview (read-only, inherited from selected KPI) --}}
<div id="kpi_details_panel" class="hidden rounded-2xl border border-violet-100 bg-violet-50/60 p-5">
    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Thông tin từ KPI đã chọn</p>
    <p class="mt-1 text-sm text-slate-600">Mục tiêu và thời gian được lấy tự động, không cần nhập lại.</p>

    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Mục tiêu</p>
            <p id="kpi_preview_target" class="mt-1 text-lg font-extrabold text-violet-700">—</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Thời gian</p>
            <p id="kpi_preview_dates" class="mt-1 text-sm font-semibold text-slate-800">—</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Phòng ban áp dụng</p>
            <p id="kpi_preview_departments" class="mt-1 text-sm font-medium text-slate-700">—</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Chức vụ áp dụng</p>
            <p id="kpi_preview_positions" class="mt-1 text-sm font-medium text-slate-700">—</p>
        </div>
    </div>

    <div id="kpi_preview_tasks_wrapper" class="mt-4 hidden rounded-xl bg-white px-4 py-3 shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Nhiệm vụ cần thực hiện</p>
        <ul id="kpi_preview_tasks" class="mt-2 space-y-1.5 text-sm text-slate-700"></ul>
    </div>

    <p id="kpi_preview_warning" class="mt-3 hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"></p>
</div>

<div id="kpi_details_empty" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-4 text-sm text-slate-500">
    Chọn KPI để tự động hiển thị mục tiêu, thời gian và phạm vi áp dụng.
</div>
