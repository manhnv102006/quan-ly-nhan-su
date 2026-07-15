@props([
    'title' => 'Dashboard',
    'subtitle' => null,
])

@include('layouts.accountant', [
    'title' => $title,
    'subtitle' => $subtitle,
    'slot' => $slot,
])
