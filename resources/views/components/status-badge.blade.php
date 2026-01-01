@props(['status'])

@php
    $status = (string) $status;

    $classes = match ($status) {
        'scheduled' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'completed' => 'bg-green-50 text-green-700 ring-green-600/20',
        'postponed' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'missed' => 'bg-red-50 text-red-700 ring-red-600/20',
        // cycles
        'awaiting_student_payment' => 'bg-gray-50 text-gray-700 ring-gray-600/20',
        'payment_submitted' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'paid' => 'bg-green-50 text-green-700 ring-green-600/20',
        'active' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'inactive' => 'bg-gray-50 text-gray-700 ring-gray-600/20',
        // payments / review
        'pending_review' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'approved' => 'bg-green-50 text-green-700 ring-green-600/20',
        'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
        // reschedule
        'pending' => 'bg-gray-50 text-gray-700 ring-gray-600/20',
        'auto_applied' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        // teacher money
        'unpaid' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'paid_out' => 'bg-green-50 text-green-700 ring-green-600/20',
        default => 'bg-gray-50 text-gray-700 ring-gray-600/20',
    };
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset {$classes}") }}>
    {{ ucfirst($status) }}
</span>

