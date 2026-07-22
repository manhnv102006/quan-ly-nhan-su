@extends('layouts.recruitment')

@section('title', 'Tin tức')
@section('header_theme', 'dark')

@section('content')
    <div class="overflow-hidden bg-[#030712] text-white">
        <section class="relative border-b border-white/10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(249,115,22,.20),transparent_32%),radial-gradient(circle_at_80%_8%,rgba(34,211,238,.20),transparent_28%)]"></div>
            <div class="relative mx-auto max-w-[1500px] px-5 py-20 sm:px-8 lg:px-12 lg:py-28">
                <p class="inline-flex rounded-full border border-orange-300/20 bg-orange-300/10 px-4 py-2 text-xs font-black uppercase tracking-[0.25em] text-orange-200">Tin tức HRM</p>
                <h1 class="mt-7 max-w-5xl text-5xl font-black leading-tight tracking-tight sm:text-7xl">Cập nhật xu hướng tuyển dụng và vận hành nhân sự</h1>
                <p class="mt-6 max-w-3xl text-base leading-8 text-slate-300">
                    Những góc nhìn ngắn gọn về trải nghiệm ứng viên, dữ liệu tuyển dụng, quản trị hồ sơ và cách HRM giúp quy trình nhân sự rõ ràng hơn.
                </p>
            </div>
        </section>

        <section class="mx-auto grid max-w-[1500px] grid-cols-1 gap-6 px-5 py-20 sm:px-8 lg:grid-cols-[1.1fr_.9fr] lg:px-12">
            <article class="min-h-[520px] rounded-[2rem] border border-white/10 bg-gradient-to-br from-orange-500/30 via-white/[0.08] to-transparent p-8 shadow-2xl shadow-orange-950/20">
                <p class="text-sm font-black uppercase tracking-[0.25em] text-orange-100">Nổi bật</p>
                <h2 class="mt-32 max-w-3xl text-4xl font-black leading-tight sm:text-6xl">Ứng tuyển không cần đăng nhập: giảm ma sát cho ứng viên mới</h2>
                <p class="mt-5 max-w-2xl text-base leading-8 text-slate-300">Một cổng tuyển dụng tốt cần giúp ứng viên tập trung vào nội dung hồ sơ thay vì thủ tục tài khoản.</p>
            </article>

            <div class="grid grid-cols-1 gap-5">
                @foreach ([
                    ['Dữ liệu tập trung giúp HR phản hồi nhanh hơn', 'Hồ sơ đi thẳng vào quản trị ứng viên, giảm thao tác nhập lại.'],
                    ['Tin tuyển dụng rõ ràng tạo niềm tin', 'Ứng viên cần thấy phòng ban, địa điểm, hạn nộp và quyền lợi trước khi quyết định.'],
                    ['CV tùy chọn nhưng form vẫn đủ thông tin', 'Giữ trải nghiệm nhẹ nhưng vẫn đảm bảo HR có dữ liệu liên hệ cần thiết.'],
                ] as $post)
                    <article class="rounded-[2rem] border border-white/10 bg-white/[0.06] p-6 transition hover:-translate-y-1 hover:border-cyan-300/50">
                        <p class="text-xs font-black uppercase tracking-[0.25em] text-cyan-300">Insight 0{{ $loop->iteration }}</p>
                        <h3 class="mt-4 text-2xl font-black">{{ $post[0] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-300">{{ $post[1] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="border-t border-white/10 bg-black">
            <div class="mx-auto max-w-[1500px] px-5 py-20 sm:px-8 lg:px-12">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-cyan-300">Bản tin tuyển dụng</p>
                        <h2 class="mt-3 text-4xl font-black sm:text-6xl">Đọc thêm</h2>
                    </div>
                    <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-white px-6 py-3 text-sm font-black uppercase text-slate-950 transition hover:-translate-y-0.5 hover:bg-slate-100 md:w-auto">Xem việc làm</a>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-5 md:grid-cols-3">
                    @foreach ([
                        'Thiết kế form ứng tuyển thân thiện trên mobile',
                        'Vì sao hạn nộp cần hiển thị rõ trên từng tin',
                        'Cách danh sách ứng viên giúp HR theo dõi dễ hơn',
                        'Tối ưu trang tuyển dụng cho ứng viên lần đầu truy cập',
                        'Kết nối tuyển dụng công khai với dữ liệu nội bộ',
                        'Những thông tin nên có trong một tin tuyển dụng',
                    ] as $title)
                        <article class="rounded-3xl border border-white/10 bg-white/[0.05] p-6">
                            <span class="rounded-full bg-orange-400/15 px-3 py-1 text-xs font-black uppercase text-orange-300">HRM News</span>
                            <h3 class="mt-5 text-xl font-black leading-7">{{ $title }}</h3>
                            <p class="mt-4 text-sm leading-7 text-slate-300">Nội dung được biên tập để giúp ứng viên và đội ngũ tuyển dụng có cùng góc nhìn về quy trình.</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
