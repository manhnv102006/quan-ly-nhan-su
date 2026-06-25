<x-admin-layout title="Tao lich phong van">
    @php
        $inputClass = 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10';
    @endphp

    <div class="max-w-5xl space-y-6">
        <section class="rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600">Tuyen dung</a>
                        <span>/</span>
                        <a href="{{ route('admin.recruitment.interviews') }}" class="hover:text-cyan-600">Phong van</a>
                        <span>/</span>
                        <span class="font-semibold text-slate-700">Tao moi</span>
                    </div>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Tao lich phong van</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Chon ung vien, nguoi phong van va thoi gian. He thong se tu dong chuyen ung vien sang trang thai phong van.
                    </p>
                </div>

                <a href="{{ route('admin.recruitment.interviews') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700">
                    Quay lai danh sach
                </a>
            </div>
        </section>

        <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <h3 class="text-base font-black text-slate-900">Thong tin lich phong van</h3>
                <p class="mt-1 text-sm text-slate-500">Cac truong co dau * la bat buoc.</p>
            </div>

            <div class="p-5 sm:p-6">
                @if (isset($errors) && $errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-bold">Vui long kiem tra lai thong tin:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.recruitment.interviews.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="candidate_id" class="mb-2 block text-sm font-bold text-slate-700">Ung vien <span class="text-red-500">*</span></label>
                        <select id="candidate_id" name="candidate_id" required class="{{ $inputClass }} @error('candidate_id') border-red-400 @enderror">
                            <option value="">Chon ung vien</option>
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
                        @error('candidate_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="interviewer_id" class="mb-2 block text-sm font-bold text-slate-700">Nguoi phong van</label>
                            <select id="interviewer_id" name="interviewer_id" class="{{ $inputClass }} @error('interviewer_id') border-red-400 @enderror">
                                <option value="">Chua gan nguoi phong van</option>
                                @foreach ($interviewers as $interviewer)
                                    <option value="{{ $interviewer->id }}" @selected(old('interviewer_id') == $interviewer->id)>
                                        {{ $interviewer->full_name }} ({{ $interviewer->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('interviewer_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="interview_date" class="mb-2 block text-sm font-bold text-slate-700">Thoi gian phong van <span class="text-red-500">*</span></label>
                            <input type="datetime-local" id="interview_date" name="interview_date" value="{{ old('interview_date') }}" required
                                   class="{{ $inputClass }} @error('interview_date') border-red-400 @enderror">
                            @error('interview_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-4">
                        <p class="text-sm font-bold text-cyan-800">Trang thai mac dinh</p>
                        <p class="mt-1 text-sm leading-6 text-cyan-700">
                            Lich moi se o trang thai cho ket qua. Khi cap nhat Dat hoac Khong dat, ho so ung vien se doi trang thai tu dong.
                        </p>
                    </div>

                    <div>
                        <label for="note" class="mb-2 block text-sm font-bold text-slate-700">Ghi chu</label>
                        <textarea id="note" name="note" rows="4" placeholder="Nhap ghi chu neu can"
                                  class="{{ $inputClass }} resize-y @error('note') border-red-400 @enderror">{{ old('note') }}</textarea>
                        @error('note')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:items-center">
                        <a href="{{ route('admin.recruitment.interviews') }}"
                           class="inline-flex items-center justify-center rounded-2xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200">
                            Huy
                        </a>
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                            Tao lich phong van
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</x-admin-layout>
