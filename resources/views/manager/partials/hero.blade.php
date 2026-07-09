<section {{ $attributes->merge(['class' => 'manager-hero']) }}>
    <div class="absolute -right-16 top-0 h-56 w-56 rounded-full bg-teal-500/20 blur-3xl"></div>
    <div class="absolute bottom-0 left-0 h-44 w-44 -translate-x-1/4 translate-y-1/4 rounded-full bg-emerald-400/15 blur-3xl"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.08),transparent_45%)]"></div>

    <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            @if (! empty($badge))
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-white"></span>
                    {{ $badge }}
                </span>
            @endif
            <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $heading }}</h2>
            @if (! empty($description))
                <p class="mt-3 max-w-2xl text-sm leading-6 text-teal-100/90 sm:text-base">{{ $description }}</p>
            @endif
            @if (! empty($actions))
                <div class="mt-6 flex flex-wrap gap-3">
                    {{ $actions }}
                </div>
            @endif
        </div>

        @if (! empty($aside))
            <div class="grid gap-3 sm:grid-cols-3 lg:w-[420px] lg:grid-cols-1">
                {{ $aside }}
            </div>
        @endif
    </div>
</section>
