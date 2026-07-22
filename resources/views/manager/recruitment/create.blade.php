<x-manager-layout
    title="Tạo tin tuyển dụng"
    subtitle="Điền thông tin và gửi admin duyệt trước khi hiển thị công khai."
>
    @php
        $inputClass = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20';
        $labelClass = 'mb-1.5 block text-sm font-semibold text-slate-700';
        $workTypes = [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'remote' => 'Remote',
            'hybrid' => 'Hybrid',
            'contract' => 'Hợp đồng',
        ];
    @endphp

    <div class="manager-page space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('manager.recruitment.index') }}"
               class="inline-flex items-center text-sm font-semibold text-slate-600 transition hover:text-teal-700">
                ← Quay lại tuyển dụng
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">Vui lòng kiểm tra lại:</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="manager-card overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Thông tin tin tuyển dụng</h2>
                <p class="mt-1 text-sm text-slate-500">Sau khi gửi, tin ở trạng thái <strong>Chờ admin duyệt</strong>.</p>
            </div>

            <form action="{{ route('manager.recruitment.job-posts.store') }}" method="POST" class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                @csrf

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Tiêu đề vị trí <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="VD: Lập trình viên PHP" class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Phòng ban <span class="text-red-500">*</span></label>
                    <select name="department_id" required class="{{ $inputClass }}">
                        @foreach ($managedDepartments as $department)
                            <option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>{{ $department->department_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Số lượng tuyển <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}" required class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Địa điểm làm việc</label>
                    <input type="text" name="work_location" value="{{ old('work_location') }}" placeholder="TP. Hồ Chí Minh" class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Hình thức</label>
                    <select name="work_type" class="{{ $inputClass }}">
                        <option value="">Chưa chọn</option>
                        @foreach ($workTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('work_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Lương tối thiểu (VNĐ)</label>
                    <input type="number" name="salary_min" min="0" step="1000" value="{{ old('salary_min') }}" class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">Lương tối đa (VNĐ)</label>
                    <input type="number" name="salary_max" min="0" step="1000" value="{{ old('salary_max') }}" class="{{ $inputClass }}">
                </div>

                <div class="md:col-span-2 md:max-w-xs">
                    <label class="{{ $labelClass }}">Hạn nộp hồ sơ</label>
                    <input type="date" name="application_deadline" value="{{ old('application_deadline') }}" class="{{ $inputClass }}">
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Mô tả công việc</label>
                    <textarea name="description" rows="4" placeholder="Mô tả ngắn gọn công việc..." class="{{ $inputClass }} resize-y">{{ old('description') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Yêu cầu ứng viên</label>
                    <textarea name="requirements" rows="3" placeholder="Kinh nghiệm, kỹ năng..." class="{{ $inputClass }} resize-y">{{ old('requirements') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="{{ $labelClass }}">Quyền lợi</label>
                    <textarea name="benefits" rows="2" placeholder="Bảo hiểm, thưởng..." class="{{ $inputClass }} resize-y">{{ old('benefits') }}</textarea>
                </div>

                <div class="md:col-span-2 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <a href="{{ route('manager.recruitment.index') }}"
                       class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Hủy
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-teal-600/20 transition hover:bg-teal-700">
                        Gửi admin duyệt
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-manager-layout>
