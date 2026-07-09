@props([
    'title' => 'Dashboard',
    'subtitle' => null,
])

@include('layouts.employee', [
    'title' => $title,
    'subtitle' => $subtitle,
    'slot' => $slot,
])
