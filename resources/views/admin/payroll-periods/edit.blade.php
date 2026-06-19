<x-admin-layout title="Sửa kỳ lương">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Chỉnh sửa kỳ lương</h2>
                <p class="text-sm text-slate-500 mt-1">Thay đổi thông tin kỳ lương "{{ $payrollPeriod->name }}"</p>
            </div>

            <a href="{{ route('admin.payroll-periods.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                ← Quay lại
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sm:p-8 max-w-3xl">

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold mb-1">Vui lòng kiểm tra lại thông tin:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.payroll-periods.update', $payrollPeriod) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="month" class="block text-sm font-semibold text-slate-700 mb-2">
                            Tháng <span class="text-red-500">*</span>
                        </label>
                        <select id="month" name="month" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('month') border-red-400 @enderror">
                            <option value="">-- Chọn tháng --</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected(old('month', $payrollPeriod->month) == $m)>
                                    Tháng {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                </option>
                            @endfor
                        </select>
                        @error('month')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="year" class="block text-sm font-semibold text-slate-700 mb-2">
                            Năm <span class="text-red-500">*</span>
                        </label>
                        <select id="year" name="year" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('year') border-red-400 @enderror">
                            @for ($y = date('Y') - 1; $y <= date('Y') + 3; $y++)
                                <option value="{{ $y }}" @selected(old('year', $payrollPeriod->year) == $y)>
                                    Năm {{ $y }}
                                </option>
                            @endfor
                        </select>
                        @error('year')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
                        Tên kỳ lương <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $payrollPeriod->name) }}"
                           placeholder="Nhập tên kỳ lương" required
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('name') border-red-400 @enderror">
                    @error('name')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-slate-700 mb-2">
                            Ngày bắt đầu <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="start_date" name="start_date"
                               value="{{ old('start_date', $payrollPeriod->start_date?->format('Y-m-d')) }}" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('start_date') border-red-400 @enderror">
                        @error('start_date')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-semibold text-slate-700 mb-2">
                            Ngày kết thúc <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="end_date" name="end_date"
                               value="{{ old('end_date', $payrollPeriod->end_date?->format('Y-m-d')) }}" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('end_date') border-red-400 @enderror">
                        @error('end_date')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
                        Trạng thái <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('status') border-red-400 @enderror">
                        <option value="open" @selected(old('status', $payrollPeriod->status) === 'open')>Đang mở (Open)</option>
                        <option value="closed" @selected(old('status', $payrollPeriod->status) === 'closed')>Đã khóa (Closed)</option>
                    </select>
                    @error('status')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                        ✓ Lưu thay đổi
                    </button>
                    <a href="{{ route('admin.payroll-periods.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                        Hủy
                    </a>
                </div>
            </form>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const monthSelect = document.getElementById('month');
            const yearSelect = document.getElementById('year');
            const nameInput = document.getElementById('name');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            function autofillDates() {
                const month = monthSelect.value;
                const year = yearSelect.value;

                if (month && year) {
                    const formattedMonth = month.toString().padStart(2, '0');

                    // Tự động điền Tên kỳ lương (nếu user chưa sửa tay)
                    if (!nameInput.dataset.manual) {
                        nameInput.value = `Kỳ lương tháng ${formattedMonth}/${year}`;
                    }

                    // Tự động tính Ngày bắt đầu (01 của tháng đó)
                    startDateInput.value = `${year}-${formattedMonth}-01`;

                    // Tự động tính Ngày kết thúc (ngày cuối của tháng đó)
                    const lastDay = new Date(year, month, 0).getDate();
                    endDateInput.value = `${year}-${formattedMonth}-${lastDay.toString().padStart(2, '0')}`;
                }
            }

            // Đánh dấu là đã sửa tay nếu lúc tải trang tên khác cấu trúc chuẩn (hoặc khi user tự sửa)
            const currentFormattedMonth = monthSelect.value.toString().padStart(2, '0');
            const standardName = `Kỳ lương tháng ${currentFormattedMonth}/${yearSelect.value}`;
            if (nameInput.value !== standardName) {
                nameInput.dataset.manual = 'true';
            }

            // Lắng nghe thay đổi
            monthSelect.addEventListener('change', autofillDates);
            yearSelect.addEventListener('change', autofillDates);

            nameInput.addEventListener('input', function () {
                nameInput.dataset.manual = 'true';
            });
        });
    </script>

</x-admin-layout>
