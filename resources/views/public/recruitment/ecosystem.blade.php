@extends('layouts.recruitment')

@section('title', 'Hệ sinh thái HRM')
@section('header_theme', 'dark')

@section('content')
    <div class="overflow-hidden bg-[#030712] text-white">
        <section class="relative border-b border-white/10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_12%_18%,rgba(34,211,238,.22),transparent_30%),radial-gradient(circle_at_88%_24%,rgba(249,115,22,.18),transparent_30%)]"></div>
            <div class="relative mx-auto max-w-[1500px] px-5 py-20 sm:px-8 lg:px-12 lg:py-28">
                <p class="inline-flex rounded-full border border-cyan-300/20 bg-cyan-300/10 px-4 py-2 text-xs font-black uppercase tracking-[0.25em] text-cyan-200">Hệ sinh thái HRM</p>
                <h1 class="mt-7 max-w-5xl text-5xl font-black leading-tight tracking-tight sm:text-7xl">Một hệ thống nhân sự liền mạch từ tuyển dụng đến vận hành</h1>
                <p class="mt-6 max-w-3xl text-base leading-8 text-slate-300">
                    HRM kết nối các nghiệp vụ nhân sự quan trọng trong cùng một trải nghiệm: tuyển dụng, hồ sơ ứng viên, nhân viên, chấm công, nghỉ phép và quản trị dữ liệu.
                </p>
            </div>
        </section>

        <section class="mx-auto max-w-[1500px] px-5 py-20 sm:px-8 lg:px-12">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['Tuyển dụng', 'Đăng tin, nhận hồ sơ, theo dõi ứng viên mới từ website công khai.', 'from-cyan-400/30'],
                    ['Nhân sự', 'Quản lý hồ sơ nhân viên, phòng ban, vị trí và thông tin làm việc.', 'from-orange-400/30'],
                    ['Chấm công', 'Ghi nhận thời gian làm việc, hỗ trợ vận hành minh bạch hơn.', 'from-emerald-400/30'],
                    ['Quy trình', 'Đưa dữ liệu về một nơi để quản trị và ra quyết định nhanh hơn.', 'from-purple-400/30'],
                ] as $item)
                    <article class="min-h-80 rounded-[2rem] border border-white/10 bg-gradient-to-br {{ $item[2] }} via-white/[0.07] to-transparent p-7 transition duration-300 hover:-translate-y-2 hover:border-cyan-300/50">
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-white/55">Module 0{{ $loop->iteration }}</p>
                        <h2 class="mt-20 text-3xl font-black">{{ $item[0] }}</h2>
                        <p class="mt-4 text-sm leading-7 text-slate-300">{{ $item[1] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="border-y border-white/10 bg-black">
            <div class="mx-auto grid max-w-[1500px] grid-cols-1 gap-10 px-5 py-20 sm:px-8 lg:grid-cols-[.8fr_1.2fr] lg:px-12">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-orange-300">Luồng dữ liệu</p>
                    <h2 class="mt-3 text-4xl font-black tracking-tight sm:text-6xl">Không để thông tin ứng viên bị rời rạc</h2>
                    <p class="mt-5 text-base leading-8 text-slate-300">Khi ứng viên ứng tuyển, hồ sơ được đưa thẳng vào danh sách quản trị. HR có thể tiếp tục xử lý mà không cần nhập lại thủ công.</p>
                </div>

                <div class="space-y-4">
                    @foreach (['Website tuyển dụng công khai', 'Form ứng tuyển không cần đăng nhập', 'Danh sách ứng viên trong admin', 'HR đánh giá và cập nhật trạng thái'] as $flow)
                        <div class="flex items-center gap-4 rounded-3xl border border-white/10 bg-white/[0.06] p-5">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-cyan-300 text-lg font-black text-slate-950">{{ $loop->iteration }}</span>
                            <p class="text-lg font-black">{{ $flow }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-[1500px] px-5 py-20 sm:px-8 lg:px-12">
            <div class="rounded-[2rem] border border-white/10 bg-white/[0.06] p-8 text-center shadow-2xl shadow-cyan-950/30">
                <p class="text-sm font-black uppercase tracking-[0.25em] text-cyan-300">HRM Careers</p>
                <h2 class="mx-auto mt-4 max-w-4xl text-4xl font-black sm:text-6xl">Cổng tuyển dụng là cửa vào của toàn bộ hệ sinh thái nhân sự</h2>
                <a href="{{ route('public.recruitment.jobs') }}" class="mt-8 inline-flex items-center justify-center rounded-2xl bg-orange-500 px-8 py-4 text-sm font-black uppercase text-white transition hover:-translate-y-0.5 hover:bg-orange-600">Khám phá cơ hội</a>
            </div>
        </section>
    </div>
@endsection
