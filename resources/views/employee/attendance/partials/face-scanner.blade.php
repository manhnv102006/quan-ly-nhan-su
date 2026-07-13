<div
    id="face-attendance-scanner"
    class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden"
    data-enabled="{{ ($canFaceScan ?? false) ? '1' : '0' }}"
    data-show-camera="{{ ($faceEnrolled ?? false) ? '1' : '0' }}"
    data-scan-url="{{ route('attendance.face-scan') }}"
    data-interval-ms="4000"
>
    <div class="p-6 pb-4">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h2 class="text-base font-semibold text-slate-800">Chấm công bằng khuôn mặt</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Nhìn vào camera — hệ thống tự nhận diện và check-in/check-out, không cần bấm nút.
                </p>
            </div>
            @if ($canFaceScan ?? false)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-[11px] font-bold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Đang quét tự động
                </span>
            @endif
        </div>
    </div>

    @if (! ($faceEnrolled ?? false))
        <div class="px-6 pb-6">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-5 text-sm text-amber-800">
                Bạn chưa đăng ký khuôn mặt. Liên hệ quản trị viên để đăng ký trước khi dùng chấm công tự động.
            </div>
        </div>
    @else
        @if (! ($canFaceScan ?? false))
            <div class="px-6">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600 mb-4">
                    Hiện không trong khung giờ chấm công hoặc bạn đã hoàn tất chấm công hôm nay.
                    Camera vẫn bật để xem trước — dùng nút check-in/check-out thủ công bên dưới nếu cần.
                </div>
            </div>
        @endif

        <div class="px-6 pb-6">
            <div class="relative mx-auto max-w-md">
                <div
                    data-face-ring
                    class="absolute -inset-1 rounded-[1.35rem] bg-gradient-to-r from-sky-400 via-indigo-400 to-violet-400 opacity-40 blur-sm transition-opacity duration-300"
                ></div>
                <div class="relative overflow-hidden rounded-2xl border-2 border-slate-200 bg-slate-900 aspect-[4/3]">
                    <video
                        class="h-full w-full object-cover mirror"
                        playsinline
                        muted
                        autoplay
                    ></video>
                    <canvas class="hidden"></canvas>

                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <div class="h-40 w-40 rounded-full border-2 border-white/70 border-dashed"></div>
                    </div>

                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent px-4 py-3">
                        <p data-face-status class="text-center text-xs font-medium text-white">
                            Đang mở camera...
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @push('head')
            <style>
                #face-attendance-scanner .mirror {
                    transform: scaleX(-1);
                }
                #face-attendance-scanner [data-face-ring].scanning {
                    opacity: 0.85;
                    animation: face-pulse 1.2s ease-in-out infinite;
                }
                @keyframes face-pulse {
                    0%, 100% { opacity: 0.45; }
                    50% { opacity: 0.9; }
                }
            </style>
        @endpush

        @push('scripts')
            @vite('resources/js/face-attendance.js')
        @endpush
    @endif
</div>
