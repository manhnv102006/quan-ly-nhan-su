<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Tuyen dung') - {{ config('app.name', 'Quan ly nhan su') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $headerTheme = trim($__env->yieldContent('header_theme', 'light'));
        $isDarkHeader = $headerTheme === 'dark';
        $activeNav = $isDarkHeader ? 'text-cyan-300' : 'text-cyan-700';
        $activeMobile = $isDarkHeader ? 'bg-white/10 text-cyan-300' : 'bg-cyan-50 text-cyan-700';
        $navHover = $isDarkHeader ? 'hover:text-cyan-300' : 'hover:text-cyan-700';
    @endphp

    <body class="{{ $isDarkHeader ? 'bg-[#030712] text-white' : 'bg-white text-slate-900' }} font-sans antialiased">
        <div class="min-h-screen">
            <header class="sticky top-0 z-40 border-b {{ $isDarkHeader ? 'border-white/10 bg-[#030712]/95 text-white backdrop-blur' : 'border-slate-100 bg-white text-slate-900' }}">
                <div class="mx-auto flex min-h-24 w-[83%] items-center justify-between gap-6 py-5">
                    <a href="{{ route('public.recruitment.index') }}" class="flex min-w-0 items-center gap-3">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 {{ $isDarkHeader ? 'ring-white/20' : 'ring-slate-200' }}">
                            <x-application-logo class="h-14 w-14 object-contain" />
                        </div>
                        <div class="hidden min-w-0 sm:block">
                            <p class="truncate text-sm font-bold {{ $isDarkHeader ? 'text-white/55' : 'text-slate-500' }}">{{ config('app.name', 'Laravel') }}</p>
                            <p class="truncate text-xl font-black {{ $isDarkHeader ? 'text-white' : 'text-slate-950' }}">Careers</p>
                        </div>
                    </a>

                    <nav class="hidden items-center gap-8 text-base font-black uppercase {{ $isDarkHeader ? 'text-white' : 'text-slate-950' }} lg:flex">
                        <a href="{{ route('public.recruitment.index') }}" class="transition {{ $navHover }} {{ request()->routeIs('public.recruitment.index') ? $activeNav : '' }}">Trang chủ</a>
                        <a href="{{ route('public.recruitment.about') }}" class="transition {{ $navHover }} {{ request()->routeIs('public.recruitment.about') ? $activeNav : '' }}">Về HRM</a>
                        <a href="{{ route('public.recruitment.ecosystem') }}" class="transition {{ $navHover }} {{ request()->routeIs('public.recruitment.ecosystem') ? $activeNav : '' }}">Hệ sinh thái HRM</a>
                        <a href="{{ route('public.recruitment.news') }}" class="transition {{ $navHover }} {{ request()->routeIs('public.recruitment.news') ? $activeNav : '' }}">Tin tức</a>
                        <a href="{{ route('public.recruitment.jobs') }}" class="transition {{ $navHover }} {{ request()->routeIs('public.recruitment.jobs', 'public.recruitment.show', 'public.recruitment.apply') ? $activeNav : '' }}">Cơ hội nghề nghiệp</a>
                    </nav>

                    <div class="flex items-center gap-3">
                        <button type="button" class="hidden h-11 w-11 items-center justify-center rounded-full {{ $isDarkHeader ? 'text-white/55 hover:bg-white/10 hover:text-cyan-300' : 'text-slate-400 hover:bg-slate-100 hover:text-cyan-700' }} transition sm:inline-flex" aria-label="Tìm kiếm">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-3.5-3.5"></path>
                            </svg>
                        </button>
                        <span class="hidden text-sm font-black {{ $isDarkHeader ? 'text-white' : 'text-slate-950' }} sm:inline-flex">VI</span>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-xl border px-4 py-2.5 text-sm font-black transition {{ $isDarkHeader ? 'border-white/15 bg-white/5 text-white hover:border-cyan-300/60 hover:bg-cyan-300/10 hover:text-cyan-300' : 'border-slate-200 bg-white text-slate-700 hover:border-cyan-300 hover:text-cyan-700' }}">Đăng nhập</a>
                    </div>
                </div>

                <nav class="flex gap-2 overflow-x-auto border-t {{ $isDarkHeader ? 'border-white/10 text-white/80' : 'border-slate-100 text-slate-700' }} px-4 py-3 text-sm font-black uppercase lg:hidden">
                    <a href="{{ route('public.recruitment.index') }}" class="shrink-0 rounded-xl px-3 py-2 {{ request()->routeIs('public.recruitment.index') ? $activeMobile : '' }}">Trang chủ</a>
                    <a href="{{ route('public.recruitment.about') }}" class="shrink-0 rounded-xl px-3 py-2 {{ request()->routeIs('public.recruitment.about') ? $activeMobile : '' }}">Về HRM</a>
                    <a href="{{ route('public.recruitment.ecosystem') }}" class="shrink-0 rounded-xl px-3 py-2 {{ request()->routeIs('public.recruitment.ecosystem') ? $activeMobile : '' }}">Hệ sinh thái</a>
                    <a href="{{ route('public.recruitment.news') }}" class="shrink-0 rounded-xl px-3 py-2 {{ request()->routeIs('public.recruitment.news') ? $activeMobile : '' }}">Tin tức</a>
                    <a href="{{ route('public.recruitment.jobs') }}" class="shrink-0 rounded-xl px-3 py-2 {{ request()->routeIs('public.recruitment.jobs', 'public.recruitment.show', 'public.recruitment.apply') ? $activeMobile : '' }}">Cơ hội nghề nghiệp</a>
                </nav>
            </header>

            <main>
                @yield('content')
            </main>

            <footer class="border-t {{ $isDarkHeader ? 'border-white/10 bg-[#030712] text-slate-400' : 'border-slate-200 bg-white text-slate-500' }}">
                <div class="mx-auto flex w-[83%] flex-col gap-4 py-8 text-sm lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="font-black {{ $isDarkHeader ? 'text-white' : 'text-slate-900' }}">{{ config('app.name', 'Laravel') }}</p>
                        <p class="mt-1">Kết nối nhân tài với các cơ hội phù hợp.</p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                        <a href="{{ route('public.recruitment.index') }}" class="font-bold {{ $navHover }}">Trang chủ</a>
                        <a href="{{ route('public.recruitment.about') }}" class="font-bold {{ $navHover }}">Giới thiệu</a>
                        <a href="{{ route('public.recruitment.ecosystem') }}" class="font-bold {{ $navHover }}">Hệ sinh thái</a>
                        <a href="{{ route('public.recruitment.news') }}" class="font-bold {{ $navHover }}">Tin tức</a>
                        <a href="{{ route('public.recruitment.jobs') }}" class="font-bold {{ $navHover }}">Tuyển dụng</a>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
