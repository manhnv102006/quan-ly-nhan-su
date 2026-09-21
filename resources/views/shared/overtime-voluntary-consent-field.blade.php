<div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
    <label class="flex items-start gap-3 cursor-pointer">
        <input type="checkbox"
               name="voluntary_consent"
               value="1"
               @checked(old('voluntary_consent'))
               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-400/40"
               required>
        <span class="text-sm text-slate-700 leading-relaxed">
            Tôi xác nhận <strong class="font-semibold text-slate-800">đồng ý làm thêm giờ tự nguyện</strong>
            theo khung giờ đã khai báo và hiểu rằng công ty chỉ ghi nhận OT khi có sự đồng thuận này.
        </span>
    </label>
    @error('voluntary_consent')
        <p class="mt-2 text-rose-600 text-xs">{{ $message }}</p>
    @enderror
</div>
