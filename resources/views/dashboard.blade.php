<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($metrics)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="rounded-lg border border-gray-200 p-4">
                                <div class="text-sm text-gray-500">{{ __('Total sales (total invoice issued)') }}</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">
                                    RM {{ number_format($metrics['total_sales_cents'] / 100, 2) }}
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 p-4">
                                <div class="text-sm text-gray-500">{{ __('Total collection (marked paid)') }}</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">
                                    RM {{ number_format($metrics['total_collected_cents'] / 100, 2) }}
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 p-4">
                                <div class="text-sm text-gray-500">{{ __('Total pending payment') }}</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">
                                    RM {{ number_format($metrics['total_pending_cents'] / 100, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <div class="text-sm font-medium text-gray-700 mb-3">{{ __('Pending payment aging') }}</div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Bucket') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Count') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($metrics['pending_buckets'] as $b)
                                            <tr>
                                                <td class="px-4 py-3 text-sm text-gray-700">{{ $b['label'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-700">{{ $b['count'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-700">RM {{ number_format($b['amount_cents'] / 100, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                {{ __('Aging is calculated from invoice (cycle) created date.') }}
                            </div>
                        </div>

                        <div class="mt-10">
                            <div class="text-sm font-medium text-gray-700 mb-3">{{ __('Renewal reminders (reached last lesson)') }}</div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Plan') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Cycle') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Progress') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Updated') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse(($metrics['renewals'] ?? collect()) as $r)
                                            <tr>
                                                <td class="px-4 py-3 text-sm text-gray-700">{{ $r['student'] ?? '—' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-700">{{ $r['plan'] ?? '—' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-700">#{{ $r['cycle_number'] ?? '—' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-700">{{ $r['lessons_per_cycle'] ?? '—' }}/{{ $r['lessons_per_cycle'] ?? '—' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-700">{{ $r['completed_at']?->format('Y-m-d') ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No renewals to show.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                {{ __('This list shows cycles marked completed (e.g. 4/4 or 2/2).') }}
                            </div>
                        </div>
                    @else
                        {{ __("You're logged in!") }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
