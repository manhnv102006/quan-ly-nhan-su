@props([
    'title' => 'Dashboard',
    'subtitle' => null,
])

@include('layouts.manager', [
    'title' => $title,
    'subtitle' => $subtitle,
    'slot' => $slot,
])
