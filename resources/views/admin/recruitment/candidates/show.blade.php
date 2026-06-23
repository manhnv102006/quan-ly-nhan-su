<x-admin-layout title="Chi tiết ứng viên">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600 transition">Tuyển dụng</a>
                    <span>/</span>
                    <a href="{{ route('admin.recruitment.candidates') }}" class="hover:text-cyan-600 transition">Ứng viên</a>
                    <span>/</span>
                    <span class="text-slate-700 font-medium">{{ $candidate->full_name }}</span>
                </div>

                <h2 class="mt-2 text-2xl font-bold text-slate-800">Chi tiết ứng viên</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Xem toàn bộ thông tin hồ sơ ứng viên, tin tuyển dụng liên kết và tình trạng CV hiện tại.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if ($cvUrl)
                    <a href="{{ $cvUrl }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-cyan-600 text-white font-medium hover:bg-cyan-700 transition shadow-lg shadow-cyan-500/20">
                        Mở CV
                    </a>
                @endif

                <a href="{{ route('admin.recruitment.candidates.edit', $candidate) }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-amber-100 text-amber-700 font-medium hover:bg-amber-200 transition">
                    Sửa ứng viên
                </a>

                <form action="{{ route('admin.recruitment.candidates.destroy', $candidate) }}" method="POST"
                      onsubmit="return confirm('Bạn có chắc muốn xóa ứng viên này? Các lịch phỏng vấn liên quan cũng sẽ bị xóa.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-red-100 text-red-700 font-medium hover:bg-red-200 transition">
                        Xóa ứng viên
                    </button>
                </form>

                <a href="{{ route('admin.recruitment.candidates') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                    Quay lại danh sách
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="flex items-center gap-3 bg-white border border-emerald-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 bg-white border border-red-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-800">Thông tin ứng viên</h3>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Họ và tên</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-semibold text-slate-800">{{ $candidate->full_name }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Trạng thái</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    @if ($candidate->status === 'new')
                                        <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">Mới</span>
                                    @elseif ($candidate->status === 'interview')
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Phỏng vấn</span>
                                    @elseif ($candidate->status === 'passed')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Đạt</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Không đạt</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Số điện thoại</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">{{ $candidate->phone }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Email</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">{{ $candidate->email }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Ngày sinh</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">{{ $candidate->birth_date?->format('d/m/Y') ?? 'Chưa cập nhật' }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Ngày tạo hồ sơ</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">{{ $candidate->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-500 mb-2">Địa chỉ</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 min-h-14">{{ $candidate->address }}</div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-500 mb-2">Cập nhật lần cuối</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">{{ $candidate->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-800">Thông tin tuyển dụng liên kết</h3>
                    </div>

                    <div class="p-6">
                        @if ($candidate->jobPost)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-slate-500 mb-2">Tin tuyển dụng</label>
                                    <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-medium text-slate-800">{{ $candidate->jobPost->title }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-500 mb-2">Trạng thái tin</label>
                                    <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                        @if ($candidate->jobPost->status === 'open')
                                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Đang mở</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">Đã đóng</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-500 mb-2">Phòng ban</label>
                                    <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">{{ $candidate->jobPost->department?->department_name ?? 'Chưa xác định phòng ban' }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-500 mb-2">Số lượng tuyển</label>
                                    <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">{{ $candidate->jobPost->quantity ?? '-' }}</div>
                                </div>
                            </div>
                        @else
                            <div class="py-10 text-center text-slate-500">Ứng viên này chưa được gắn với tin tuyển dụng nào.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                @php
                    $parts = collect(preg_split('/\s+/', trim($candidate->full_name)))->filter();
                    $initial = $parts->isNotEmpty() ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $parts->last(), 0, 1)) : 'UV';
                @endphp

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 text-center">
                    <div class="w-24 h-24 mx-auto rounded-3xl bg-cyan-100 flex items-center justify-center text-3xl font-bold text-cyan-700">{{ $initial }}</div>
                    <h3 class="mt-5 text-xl font-bold text-slate-800">{{ $candidate->full_name }}</h3>
                    <p class="text-slate-500 mt-2">{{ $candidate->email }}</p>
                    <div class="mt-4">
                        @if ($candidate->status === 'new')
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-sky-100 text-sky-700 text-sm font-semibold">Ứng viên mới</span>
                        @elseif ($candidate->status === 'interview')
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-amber-100 text-amber-700 text-sm font-semibold">Đang phỏng vấn</span>
                        @elseif ($candidate->status === 'passed')
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold">Ứng viên đạt</span>
                        @else
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-rose-100 text-rose-700 text-sm font-semibold">Không đạt</span>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-800">Tình trạng CV</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        @if ($cvUrl)
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                                <p class="text-sm font-semibold text-emerald-700">CV đã được tải lên</p>
                                <p class="mt-1 text-sm text-emerald-600 break-all">{{ $candidate->cv_file }}</p>
                            </div>
                            <a href="{{ $cvUrl }}" target="_blank" rel="noopener"
                               class="inline-flex w-full items-center justify-center gap-2 px-5 py-3 rounded-xl bg-cyan-600 text-white font-medium hover:bg-cyan-700 transition">
                                Mở CV trong tab mới
                            </a>
                        @elseif ($candidate->cv_file)
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                                <p class="text-sm font-semibold text-amber-700">Đã có đường dẫn CV nhưng file không còn trên storage</p>
                                <p class="mt-1 text-sm text-amber-600 break-all">{{ $candidate->cv_file }}</p>
                            </div>
                        @else
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-sm font-semibold text-slate-700">Chưa có CV</p>
                                <p class="mt-1 text-sm text-slate-500">Ứng viên này chưa tải lên file CV trong hệ thống.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

</x-admin-layout>