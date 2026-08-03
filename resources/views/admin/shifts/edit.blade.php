<x-admin-layout title="Cập nhật ca làm việc">

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Cập nhật ca làm việc</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $shift->shift_name }}</p>
            </div>
            <a href="{{ route('admin.shifts.index') }}" class="admin-btn-secondary">← Quay lại</a>
        </div>

        <div class="admin-card max-w-3xl p-5 sm:p-6">
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <p class="font-semibold">Không thể cập nhật ca làm việc:</p>
                    <ul class="mt-1 list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.shifts.update', $shift) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="shift_name" class="admin-label">Tên ca *</label>
                    <input type="text" id="shift_name" name="shift_name" class="admin-field @error('shift_name') border-rose-400 @enderror"
                           value="{{ old('shift_name', $shift->shift_name) }}" required maxlength="100">
                    @error('shift_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="start_time" class="admin-label">Giờ bắt đầu *</label>
                        <input type="time" id="start_time" name="start_time" class="admin-field @error('start_time') border-rose-400 @enderror"
                               value="{{ old('start_time', $shift->start_time?->format('H:i')) }}" required>
                        @error('start_time')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="end_time" class="admin-label">Giờ kết thúc *</label>
                        <input type="time" id="end_time" name="end_time" class="admin-field @error('end_time') border-rose-400 @enderror"
                               value="{{ old('end_time', $shift->end_time?->format('H:i')) }}" required>
                        @error('end_time')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-5">
                    <button type="submit" class="admin-btn-violet px-6">Cập nhật</button>
                    <a href="{{ route('admin.shifts.index') }}" class="admin-btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>
