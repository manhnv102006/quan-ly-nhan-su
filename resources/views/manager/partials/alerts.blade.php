@if (session('success'))
    <div class="manager-alert-success">
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="manager-alert-error">
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="manager-alert-error">
        <ul class="list-disc space-y-1 ps-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
