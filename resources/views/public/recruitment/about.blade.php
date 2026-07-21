@extends('layouts.recruitment')

@section('title', 'Giới thiệu')

@section('content')
    <section class="bg-white">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(320px,420px)] lg:items-center lg:px-8 lg:py-16">
            <div class="min-w-0">
                <p class="text-sm font-black uppercase tracking-wide text-cyan-700">Giới thiệu</p>
                <h1 class="mt-4 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl">Môi trường làm việc rõ ràng, minh bạch và hướng đến con người</h1>
                <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">
                    Cổng tuyển dụng giúp ứng viên tìm hiểu vị trí phù hợp, gửi hồ sơ nhanh chóng và được bộ phận nhân sự tiếp nhận trực tiếp trên hệ thống quản trị.
                </p>
            </div>

            <div class="rounded-2xl border border-cyan-100 bg-cyan-50 p-6">
                <h2 class="text-xl font-black text-slate-950">Điều chúng tôi ưu tiên</h2>
                <div class="mt-5 space-y-4 text-sm leading-6 text-slate-700">
                    <p><span class="font-black text-slate-950">Minh bạch:</span> tin tuyển dụng có thông tin vị trí, phòng ban, hạn nộp và quyền lợi rõ ràng.</p>
                    <p><span class="font-black text-slate-950">Nhanh gọn:</span> ứng viên có thể gửi hồ sơ mà không cần tạo tài khoản.</p>
                    <p><span class="font-black text-slate-950">Tập trung:</span> hồ sơ được chuyển thẳng vào danh sách ứng viên để HR xử lý.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">Quy trình rõ ràng</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">Ứng viên xem tin, gửi hồ sơ, HR tiếp nhận và cập nhật trạng thái trong hệ thống.</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">Dữ liệu tập trung</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">Mỗi hồ sơ gửi từ website đều tự động xuất hiện trong trang quản trị ứng viên.</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">Trải nghiệm dễ dùng</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">Giao diện tối giản, dễ đọc trên điện thoại và máy tính, phù hợp cho ứng viên lần đầu truy cập.</p>
            </div>
        </div>
    </section>
@endsection
