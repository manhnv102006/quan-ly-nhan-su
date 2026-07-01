<form method="GET" action="{{ $filterAction }}"
      class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-end gap-3">
        <div>
            <label for="month" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Tháng</label>
            <select id="month" name="month" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" @selected($month == $i)>Tháng {{ $i }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label for="year" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Năm</label>
            <input id="year" type="number" name="year" value="{{ $year }}" min="2020" max="2100"
                   class="w-28 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
        </div>
        <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-700 transition">
            Xem báo cáo
        </button>
    </div>
</form>
