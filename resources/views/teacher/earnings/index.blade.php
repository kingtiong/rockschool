<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Earnings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-wrap gap-6 text-sm">
                        <div>
                            <div class="text-gray-500">{{ __('Unpaid') }}</div>
                            <div class="text-lg font-semibold text-gray-800">RM {{ number_format($unpaidTotalCents / 100, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">{{ __('Paid') }}</div>
                            <div class="text-lg font-semibold text-gray-800">RM {{ number_format($paidTotalCents / 100, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Lesson') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Payout') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($earnings as $e)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            <div class="font-medium text-gray-800">{{ $e->lesson?->scheduled_start_at?->format('D, j M Y g:i A') ?? '—' }}</div>
                                            <div class="text-gray-500">{{ $e->lesson?->minutes ?? '—' }} {{ __('mins') }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $e->lesson?->student?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">RM {{ number_format($e->amount_cents / 100, 2) }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <x-status-badge :status="$e->status" />
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            @if($e->payout)
                                                #{{ $e->payout->id }} <x-status-badge :status="$e->payout->status" />
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No earnings yet. Earnings appear after you mark lessons completed.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

