<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        @php
            $isWide = filter_var($attributes->get('wide', false), FILTER_VALIDATE_BOOLEAN);
        @endphp

        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-10 bg-gradient-to-br from-indigo-600 via-blue-600 to-cyan-500">
            <div class="text-center mb-6">
                <a href="/" class="inline-flex flex-col items-center group">
                    <div class="w-24 h-24 rounded-3xl overflow-hidden bg-white flex items-center justify-center shadow-lg group-hover:scale-105 transition duration-300">
                        <x-application-logo class="w-full h-full object-contain p-3" />
                    </div>
                    <span class="mt-3 text-xl font-bold text-white tracking-tight">{{ config('app.name', 'Quản lý nhân sự') }}</span>
                </a>
            </div>

            <div class="w-full {{ $isWide ? 'max-w-6xl' : 'sm:max-w-md' }}">
                <div class="bg-white/95 backdrop-blur-sm shadow-2xl rounded-2xl overflow-hidden border border-white/20">
                    <div class="{{ $isWide ? 'p-4 sm:p-6' : 'px-8 pt-8 pb-2' }}">
                        {{ $slot }}
                    </div>
                </div>
            </div>

            <p class="mt-8 text-sm text-white/70">&copy; {{ date('Y') }} {{ config('app.name', 'Quản lý nhân sự') }}</p>
        </div>
    </body>
</html>
