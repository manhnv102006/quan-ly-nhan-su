<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Tuyển dụng') - {{ config('app.name', 'Quản lý nhân sự') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-slate-900 antialiased">
        <div class="min-h-screen">
            <header class="sticky top-0 z-40 border-b border-slate-100 bg-white">
                <div class="mx-auto flex min-h-24 max-w-[1500px] items-center justify-between gap-6 px-5 py-5 sm:px-8 lg:px-12">
                    <a href="{{ route('public.recruitment.index') }}" class="flex min-w-0 items-center gap-3">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                            <x-application-logo class="h-14 w-14 object-contain" />
                        </div>
                        <div class="hidden min-w-0 sm:block">
                            <p class="truncate text-sm font-bold text-slate-500">{{ config('app.name', 'Quản lý nhân sự') }}</p>
                            <p class="truncate text-xl font-black text-slate-950">Careers</p>
                        </div>
                    </a>

                    <nav class="hidden items-center gap-8 text-base font-black uppercase text-slate-950 lg:flex">
                        <a href="{{ route('public.recruitment.index') }}" class="transition hover:text-cyan-700 {{ request()->routeIs('public.recruitment.index') ? 'text-cyan-700' : '' }}">Trang chủ</a>
                        <a href="{{ route('public.recruitment.about') }}" class="transition hover:text-cyan-700 {{ request()->routeIs('public.recruitment.about') ? 'text-cyan-700' : '' }}">Về HRM</a>
                        <a href="{{ route('public.recruitment.index') }}#he-sinh-thai" class="transition hover:text-cyan-700">Hệ sinh thái HRM</a>
                        <a href="{{ route('public.recruitment.index') }}#tin-tuc" class="transition hover:text-cyan-700">Tin tức</a>
                        <a href="{{ route('public.recruitment.jobs') }}" class="transition hover:text-cyan-700 {{ request()->routeIs('public.recruitment.jobs', 'public.recruitment.show', 'public.recruitment.apply') ? 'text-cyan-700' : '' }}">Cơ hội nghề nghiệp</a>
                    </nav>

                    <div class="flex items-center gap-3">
                        <button type="button" class="hidden h-11 w-11 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-cyan-700 sm:inline-flex" aria-label="Tìm kiếm">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-3.5-3.5"></path>
                            </svg>
                        </button>
                        <span class="hidden text-sm font-black text-slate-950 sm:inline-flex">VI</span>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700 whitespace-nowrap">Đăng nhập</a>
                        <a href="{{ route('register') }}" class="hidden items-center justify-center rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-orange-600 sm:inline-flex whitespace-nowrap">Đăng ký</a>
                    </div>
                </div>

                <nav class="flex gap-2 overflow-x-auto border-t border-slate-100 px-4 py-3 text-sm font-black uppercase text-slate-700 lg:hidden">
                    <a href="{{ route('public.recruitment.index') }}" class="shrink-0 rounded-xl px-3 py-2 {{ request()->routeIs('public.recruitment.index') ? 'bg-cyan-50 text-cyan-700' : '' }}">Trang chủ</a>
                    <a href="{{ route('public.recruitment.about') }}" class="shrink-0 rounded-xl px-3 py-2 {{ request()->routeIs('public.recruitment.about') ? 'bg-cyan-50 text-cyan-700' : '' }}">Về HRM</a>
                    <a href="{{ route('public.recruitment.jobs') }}" class="shrink-0 rounded-xl px-3 py-2 {{ request()->routeIs('public.recruitment.jobs', 'public.recruitment.show', 'public.recruitment.apply') ? 'bg-cyan-50 text-cyan-700' : '' }}">Cơ hội nghề nghiệp</a>
                </nav>
            </header>

            <main>
                @yield('content')
            </main>

            <footer class="border-t border-slate-200 bg-white">
                <div class="mx-auto flex max-w-[1500px] flex-col gap-4 px-5 py-8 text-sm text-slate-500 sm:px-8 lg:flex-row lg:items-center lg:justify-between lg:px-12">
                    <div>
                        <p class="font-black text-slate-900">{{ config('app.name', 'Quản lý nhân sự') }}</p>
                        <p class="mt-1">Kết nối nhân tài với các cơ hội phù hợp.</p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                        <a href="{{ route('public.recruitment.index') }}" class="font-bold hover:text-cyan-700">Trang chủ</a>
                        <a href="{{ route('public.recruitment.about') }}" class="font-bold hover:text-cyan-700">Giới thiệu</a>
                        <a href="{{ route('public.recruitment.jobs') }}" class="font-bold hover:text-cyan-700">Tuyển dụng</a>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
