@props(['status'])

@php
    $status = (string) $status;

    $classes = match ($status) {
        'scheduled' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'completed' => 'bg-green-50 text-green-700 ring-green-600/20',
        'postponed' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'missed' => 'bg-red-50 text-red-700 ring-red-600/20',
        default => 'bg-gray-50 text-gray-700 ring-gray-600/20',
    };
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset {$classes}") }}>
    {{ ucfirst($status) }}
</span>

