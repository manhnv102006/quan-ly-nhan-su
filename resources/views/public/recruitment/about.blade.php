@extends('layouts.recruitment')

@section('title', 'Về HRM')
@section('header_theme', 'dark')

@section('content')
    <div class="overflow-hidden bg-[#030712] text-white">
        <section class="relative isolate border-b border-white/10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(34,211,238,.20),transparent_34%),radial-gradient(circle_at_80%_10%,rgba(249,115,22,.16),transparent_32%)]"></div>
            <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(to right, rgba(255,255,255,.08) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,.08) 1px, transparent 1px); background-size: 72px 72px;"></div>

            <div class="relative mx-auto grid max-w-[1500px] grid-cols-1 gap-10 px-5 py-20 sm:px-8 lg:grid-cols-[minmax(0,1fr)_minmax(380px,.72fr)] lg:px-12 lg:py-28">
                <div>
                    <p class="inline-flex rounded-full border border-cyan-300/20 bg-cyan-300/10 px-4 py-2 text-xs font-black uppercase tracking-[0.25em] text-cyan-200">Về HRM</p>
                    <h1 class="mt-7 max-w-5xl text-5xl font-black leading-tight tracking-tight sm:text-7xl">Nền tảng nhân sự số đặt con người ở trung tâm</h1>
                    <p class="mt-6 max-w-3xl text-base leading-8 text-slate-300">
                        HRM Careers kết nối ứng viên với các vị trí phù hợp, rút ngắn thời gian ứng tuyển và giúp bộ phận nhân sự tiếp nhận hồ sơ trực tiếp trên hệ thống quản trị.
                    </p>
                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-7 py-4 text-sm font-black uppercase text-white transition hover:-translate-y-0.5 hover:bg-orange-600">Xem vị trí mở</a>
                        <a href="{{ route('public.recruitment.ecosystem') }}" class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/5 px-7 py-4 text-sm font-black uppercase text-white transition hover:border-cyan-300/60 hover:bg-cyan-300/10">Hệ sinh thái HRM</a>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/10 bg-white/[0.07] p-6 shadow-2xl shadow-cyan-950/40 backdrop-blur">
                    <div class="rounded-[1.5rem] border border-white/10 bg-slate-950/80 p-6">
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-orange-300">People dashboard</p>
                        <div class="mt-8 grid grid-cols-2 gap-4">
                            <div class="rounded-2xl bg-white/[0.06] p-5">
                                <p class="text-4xl font-black text-cyan-300">01</p>
                                <p class="mt-2 text-sm font-bold text-slate-300">Tuyển dụng minh bạch</p>
                            </div>
                            <div class="rounded-2xl bg-white/[0.06] p-5">
                                <p class="text-4xl font-black text-orange-300">02</p>
                                <p class="mt-2 text-sm font-bold text-slate-300">Hồ sơ tập trung</p>
                            </div>
                            <div class="rounded-2xl bg-white/[0.06] p-5">
                                <p class="text-4xl font-black text-emerald-300">03</p>
                                <p class="mt-2 text-sm font-bold text-slate-300">Quy trình rõ ràng</p>
                            </div>
                            <div class="rounded-2xl bg-white/[0.06] p-5">
                                <p class="text-4xl font-black text-purple-300">04</p>
                                <p class="mt-2 text-sm font-bold text-slate-300">Trải nghiệm hiện đại</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-[1500px] px-5 py-20 sm:px-8 lg:px-12">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <article class="rounded-[2rem] border border-cyan-300/20 bg-cyan-300/10 p-7 transition hover:-translate-y-1 hover:border-cyan-300/60">
                    <h2 class="text-2xl font-black">Minh bạch</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-300">Tin tuyển dụng có phòng ban, địa điểm, số lượng, hình thức làm việc, lương và hạn nộp rõ ràng.</p>
                </article>
                <article class="rounded-[2rem] border border-orange-300/20 bg-orange-300/10 p-7 transition hover:-translate-y-1 hover:border-orange-300/60">
                    <h2 class="text-2xl font-black">Nhanh gọn</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-300">Ứng viên gửi hồ sơ không cần tài khoản, CV là tùy chọn và thông tin được kiểm tra trước khi gửi.</p>
                </article>
                <article class="rounded-[2rem] border border-emerald-300/20 bg-emerald-300/10 p-7 transition hover:-translate-y-1 hover:border-emerald-300/60">
                    <h2 class="text-2xl font-black">Tập trung</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-300">Hồ sơ tự động vào danh sách ứng viên trong trang quản trị để HR theo dõi và xử lý.</p>
                </article>
            </div>
        </section>

        <section class="border-t border-white/10 bg-black">
            <div class="mx-auto max-w-[1500px] px-5 py-20 sm:px-8 lg:px-12">
                <p class="text-sm font-black uppercase tracking-[0.25em] text-cyan-300">Cách HRM vận hành</p>
                <h2 class="mt-3 max-w-4xl text-4xl font-black tracking-tight sm:text-6xl">Từ dữ liệu tuyển dụng đến trải nghiệm ứng viên</h2>

                <div class="mt-12 grid grid-cols-1 gap-5 lg:grid-cols-4">
                    @foreach (['Công bố tin tuyển dụng', 'Ứng viên gửi hồ sơ', 'HR tiếp nhận dữ liệu', 'Theo dõi và phản hồi'] as $step)
                        <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-6">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-300 text-xl font-black text-slate-950">{{ $loop->iteration }}</span>
                            <h3 class="mt-6 text-xl font-black">{{ $step }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-300">Mỗi bước được tối ưu để thông tin rõ, thao tác ít và dữ liệu không bị phân tán.</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
