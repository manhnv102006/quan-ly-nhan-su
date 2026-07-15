@props([
    'title' => 'Dashboard',
    'subtitle' => null,
])

@include('layouts.leader', [
    'title' => $title,
    'subtitle' => $subtitle,
    'slot' => $slot,
])
