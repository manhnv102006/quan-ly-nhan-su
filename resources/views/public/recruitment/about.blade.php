@extends('layouts.recruitment')

@section('title', 'Về HRM')

@section('content')
    <div class="bg-white text-slate-900">
        <section class="relative overflow-hidden border-b border-slate-100 bg-gradient-to-b from-slate-50 via-white to-white">
            <div class="pointer-events-none absolute -right-24 top-0 h-72 w-72 rounded-full bg-cyan-100/60 blur-3xl"></div>
            <div class="relative mx-auto grid w-[83%] grid-cols-1 gap-10 py-14 lg:grid-cols-[minmax(0,1fr)_minmax(320px,.65fr)] lg:py-20">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-cyan-700">Về HRM</p>
                    <h1 class="mt-3 max-w-3xl text-3xl font-black leading-tight text-slate-900 sm:text-4xl lg:text-5xl">
                        Nền tảng nhân sự số đặt con người ở trung tâm
                    </h1>
                    <p class="mt-5 max-w-2xl text-base leading-relaxed text-slate-600">
                        HRM Careers kết nối ứng viên với các vị trí phù hợp, rút ngắn thời gian ứng tuyển và giúp bộ phận nhân sự tiếp nhận hồ sơ trực tiếp trên hệ thống quản trị.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-cyan-700">Xem vị trí mở</a>
                        <a href="{{ route('public.recruitment.ecosystem') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-800 transition hover:border-cyan-300">Hệ sinh thái HRM</a>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50">
                    <p class="text-xs font-bold uppercase tracking-wider text-orange-600">People dashboard</p>
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        @foreach (['Tuyển dụng minh bạch', 'Hồ sơ tập trung', 'Quy trình rõ ràng', 'Trải nghiệm hiện đại'] as $item)
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-2xl font-black text-cyan-700">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                <p class="mt-2 text-sm font-semibold text-slate-700">{{ $item }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto w-[83%] py-16">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach ([
                    ['Minh bạch', 'Tin tuyển dụng có phòng ban, địa điểm, số lượng, hình thức làm việc, lương và hạn nộp rõ ràng.', 'border-cyan-100 bg-cyan-50/40'],
                    ['Nhanh gọn', 'Ứng viên gửi hồ sơ không cần tài khoản, CV là tùy chọn và thông tin được kiểm tra trước khi gửi.', 'border-orange-100 bg-orange-50/40'],
                    ['Tập trung', 'Hồ sơ tự động vào danh sách ứng viên trong trang quản trị để HR theo dõi và xử lý.', 'border-emerald-100 bg-emerald-50/40'],
                ] as [$title, $text, $cardClass])
                    <article class="rounded-2xl border p-6 {{ $cardClass }}">
                        <h2 class="text-xl font-bold text-slate-900">{{ $title }}</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $text }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="border-t border-slate-100 bg-slate-50/50">
            <div class="mx-auto w-[83%] py-16">
                <p class="text-sm font-bold uppercase tracking-wider text-cyan-700">Cách HRM vận hành</p>
                <h2 class="mt-2 max-w-3xl text-3xl font-black text-slate-900 sm:text-4xl">Từ dữ liệu tuyển dụng đến trải nghiệm ứng viên</h2>

                <div class="mt-10 grid grid-cols-1 gap-4 lg:grid-cols-4">
                    @foreach (['Công bố tin tuyển dụng', 'Ứng viên gửi hồ sơ', 'HR tiếp nhận dữ liệu', 'Theo dõi và phản hồi'] as $step)
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-600 text-lg font-bold text-white">{{ $loop->iteration }}</span>
                            <h3 class="mt-4 text-lg font-bold text-slate-900">{{ $step }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">Mỗi bước được tối ưu để thông tin rõ, thao tác ít và dữ liệu không bị phân tán.</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
