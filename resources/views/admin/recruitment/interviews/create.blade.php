<x-admin-layout title="Tạo lịch phỏng vấn">

    <div class="space-y-6">

        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.recruitment') }}" class="hover:text-amber-600 transition">Tuyển dụng</a>
                    <span>/</span>
                    <a href="{{ route('admin.recruitment.interviews') }}" class="hover:text-amber-600 transition">Lịch phỏng vấn</a>
                    <span>/</span>
                    <span class="text-slate-700 font-medium">Tạo mới</span>
                </div>

                <h2 class="mt-2 text-2xl font-bold text-slate-800">Tạo lịch phỏng vấn</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Thiết lập lịch phỏng vấn cho ứng viên. Kết quả sẽ được để ở trạng thái chờ cập nhật.
                </p>
            </div>

            <a href="{{ route('admin.recruitment.interviews') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                Quay lại danh sách
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sm:p-8 max-w-4xl">

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

            <form action="{{ route('admin.recruitment.interviews.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="candidate_id" class="block text-sm font-semibold text-slate-700 mb-2">
                        Ứng viên <span class="text-red-500">*</span>
                    </label>
                    <select id="candidate_id" name="candidate_id" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition @error('candidate_id') border-red-400 @enderror">
                        <option value="">-- Chọn ứng viên --</option>
                        @foreach ($candidates as $candidate)
                            <option value="{{ $candidate->id }}" @selected(old('candidate_id') == $candidate->id)>
                                {{ $candidate->full_name }}
                                @if ($candidate->jobPost)
                                    - {{ $candidate->jobPost->title }}
                                @endif
                                ({{ $candidate->status }})
                            </option>
                        @endforeach
                    </select>
                    @error('candidate_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="interviewer_id" class="block text-sm font-semibold text-slate-700 mb-2">
                            Người phỏng vấn
                        </label>
                        <select id="interviewer_id" name="interviewer_id"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition @error('interviewer_id') border-red-400 @enderror">
                            <option value="">-- Chọn người phỏng vấn --</option>
                            @foreach ($interviewers as $interviewer)
                                <option value="{{ $interviewer->id }}" @selected(old('interviewer_id') == $interviewer->id)>
                                    {{ $interviewer->full_name }} ({{ $interviewer->employee_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('interviewer_id')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="interview_date" class="block text-sm font-semibold text-slate-700 mb-2">
                            Thời gian phỏng vấn <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" id="interview_date" name="interview_date"
                               value="{{ old('interview_date') }}" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition @error('interview_date') border-red-400 @enderror">
                        @error('interview_date')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                    <p class="text-sm font-semibold text-amber-700">Kết quả mặc định</p>
                    <p class="mt-1 text-sm text-amber-600">
                        Lịch phỏng vấn mới sẽ được tạo với kết quả <strong>pending</strong>. Trạng thái ứng viên sẽ chuyển sang <strong>interview</strong>.
                    </p>
                </div>

                <div>
                    <label for="note" class="block text-sm font-semibold text-slate-700 mb-2">
                        Ghi chú
                    </label>
                    <textarea id="note" name="note" rows="4"
                              placeholder="Nhập ghi chú nếu cần"
                              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition @error('note') border-red-400 @enderror">{{ old('note') }}</textarea>
                    @error('note')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-amber-600 text-white font-medium shadow-lg shadow-amber-500/20 hover:bg-amber-700 transition">
                        + Tạo lịch phỏng vấn
                    </button>
                    <a href="{{ route('admin.recruitment.interviews') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                        Hủy
                    </a>
                </div>
            </form>
        </div>

    </div>

</x-admin-layout>