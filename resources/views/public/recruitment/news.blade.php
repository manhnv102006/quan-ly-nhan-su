@extends('layouts.recruitment')

@section('title', 'Tin tức')

@section('content')
    <div class="bg-white text-slate-900">
        <section class="relative overflow-hidden border-b border-slate-100 bg-gradient-to-b from-slate-50 to-white">
            <div class="pointer-events-none absolute -left-16 bottom-0 h-56 w-56 rounded-full bg-orange-100/50 blur-3xl"></div>
            <div class="relative mx-auto w-[83%] py-14 lg:py-20">
                <p class="text-sm font-bold uppercase tracking-wider text-orange-600">Tin tức HRM</p>
                <h1 class="mt-3 max-w-3xl text-3xl font-black leading-tight text-slate-900 sm:text-4xl lg:text-5xl">
                    Cập nhật xu hướng tuyển dụng và vận hành nhân sự
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-slate-600">
                    Những góc nhìn ngắn gọn về trải nghiệm ứng viên, dữ liệu tuyển dụng, quản trị hồ sơ và cách HRM giúp quy trình nhân sự rõ ràng hơn.
                </p>
            </div>
        </section>

        <section class="mx-auto grid w-[83%] grid-cols-1 gap-6 py-16 lg:grid-cols-[1.1fr_.9fr]">
            <article class="min-h-[320px] rounded-2xl border border-orange-100 bg-gradient-to-br from-orange-50 to-white p-8 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-orange-600">Nổi bật</p>
                <h2 class="mt-8 max-w-xl text-2xl font-black leading-snug text-slate-900 sm:text-3xl">
                    Ứng tuyển không cần đăng nhập: giảm ma sát cho ứng viên mới
                </h2>
                <p class="mt-4 max-w-lg text-sm leading-relaxed text-slate-600">Một cổng tuyển dụng tốt cần giúp ứng viên tập trung vào nội dung hồ sơ thay vì thủ tục tài khoản.</p>
            </article>

            <div class="grid grid-cols-1 gap-4">
                @foreach ([
                    ['Dữ liệu tập trung giúp HR phản hồi nhanh hơn', 'Hồ sơ đi thẳng vào quản trị ứng viên, giảm thao tác nhập lại.'],
                    ['Tin tuyển dụng rõ ràng tạo niềm tin', 'Ứng viên cần thấy phòng ban, địa điểm, hạn nộp và quyền lợi trước khi quyết định.'],
                    ['CV tùy chọn nhưng form vẫn đủ thông tin', 'Giữ trải nghiệm nhẹ nhưng vẫn đảm bảo HR có dữ liệu liên hệ cần thiết.'],
                ] as $post)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-cyan-200">
                        <p class="text-xs font-bold uppercase tracking-wider text-cyan-700">Insight 0{{ $loop->iteration }}</p>
                        <h3 class="mt-2 text-lg font-bold text-slate-900">{{ $post[0] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $post[1] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="border-t border-slate-100 bg-slate-50/50">
            <div class="mx-auto w-[83%] py-16">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wider text-cyan-700">Bản tin tuyển dụng</p>
                        <h2 class="mt-2 text-3xl font-black text-slate-900">Đọc thêm</h2>
                    </div>
                    <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-cyan-700 md:w-auto">Xem việc làm</a>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-3">
                    @foreach ([
                        'Thiết kế form ứng tuyển thân thiện trên mobile',
                        'Vì sao hạn nộp cần hiển thị rõ trên từng tin',
                        'Cách danh sách ứng viên giúp HR theo dõi dễ hơn',
                        'Tối ưu trang tuyển dụng cho ứng viên lần đầu truy cập',
                        'Kết nối tuyển dụng công khai với dữ liệu nội bộ',
                        'Những thông tin nên có trong một tin tuyển dụng',
                    ] as $title)
                        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <span class="rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-bold text-orange-800">HRM News</span>
                            <h3 class="mt-3 text-base font-bold leading-snug text-slate-900">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">Nội dung được biên tập để giúp ứng viên và đội ngũ tuyển dụng có cùng góc nhìn về quy trình.</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
