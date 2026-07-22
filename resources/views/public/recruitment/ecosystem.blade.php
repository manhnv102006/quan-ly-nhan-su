@extends('layouts.recruitment')

@section('title', 'Hệ sinh thái HRM')

@section('content')
    <div class="bg-white text-slate-900">
        <section class="relative overflow-hidden border-b border-slate-100 bg-gradient-to-b from-slate-50 to-white">
            <div class="pointer-events-none absolute -right-20 top-0 h-64 w-64 rounded-full bg-orange-100/60 blur-3xl"></div>
            <div class="relative mx-auto w-[83%] py-14 lg:py-20">
                <p class="text-sm font-bold uppercase tracking-wider text-cyan-700">Hệ sinh thái HRM</p>
                <h1 class="mt-3 max-w-3xl text-3xl font-black leading-tight text-slate-900 sm:text-4xl lg:text-5xl">
                    Một hệ thống nhân sự liền mạch từ tuyển dụng đến vận hành
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-slate-600">
                    HRM kết nối các nghiệp vụ nhân sự quan trọng trong cùng một trải nghiệm: tuyển dụng, hồ sơ ứng viên, nhân viên, chấm công, nghỉ phép và quản trị dữ liệu.
                </p>
            </div>
        </section>

        <section class="mx-auto w-[83%] py-16">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['Tuyển dụng', 'Đăng tin, nhận hồ sơ, theo dõi ứng viên mới từ website công khai.'],
                    ['Nhân sự', 'Quản lý hồ sơ nhân viên, phòng ban, vị trí và thông tin làm việc.'],
                    ['Chấm công', 'Ghi nhận thời gian làm việc, hỗ trợ vận hành minh bạch hơn.'],
                    ['Quy trình', 'Đưa dữ liệu về một nơi để quản trị và ra quyết định nhanh hơn.'],
                ] as $item)
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-cyan-200 hover:shadow-md">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Module 0{{ $loop->iteration }}</p>
                        <h2 class="mt-4 text-2xl font-bold text-slate-900">{{ $item[0] }}</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $item[1] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="border-y border-slate-100 bg-slate-50/50">
            <div class="mx-auto grid w-[83%] grid-cols-1 gap-10 py-16 lg:grid-cols-[.85fr_1.15fr]">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-orange-600">Luồng dữ liệu</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">Không để thông tin ứng viên bị rời rạc</h2>
                    <p class="mt-4 text-base leading-relaxed text-slate-600">Khi ứng viên ứng tuyển, hồ sơ được đưa thẳng vào danh sách quản trị. HR có thể tiếp tục xử lý mà không cần nhập lại thủ công.</p>
                </div>

                <div class="space-y-3">
                    @foreach (['Website tuyển dụng công khai', 'Form ứng tuyển không cần đăng nhập', 'Danh sách ứng viên trong admin', 'HR đánh giá và cập nhật trạng thái'] as $flow)
                        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-600 text-sm font-bold text-white">{{ $loop->iteration }}</span>
                            <p class="font-semibold text-slate-800">{{ $flow }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mx-auto w-[83%] py-16">
            <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-cyan-50 to-white p-8 text-center shadow-sm">
                <p class="text-sm font-bold uppercase tracking-wider text-cyan-700">HRM Careers</p>
                <h2 class="mx-auto mt-3 max-w-2xl text-2xl font-black text-slate-900 sm:text-3xl">Cổng tuyển dụng là cửa vào của toàn bộ hệ sinh thái nhân sự</h2>
                <a href="{{ route('public.recruitment.jobs') }}" class="mt-6 inline-flex items-center justify-center rounded-xl bg-cyan-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-cyan-700">Khám phá cơ hội</a>
            </div>
        </section>
    </div>
@endsection
