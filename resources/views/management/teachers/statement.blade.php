<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Teacher Statement') }} — {{ $teacher->name }}
            </h2>
            <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.payouts.index') }}">
                {{ __('Back to payouts') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <form method="GET" action="{{ route('management.teachers.statement', $teacher) }}" class="flex flex-wrap items-end gap-3">
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Month') }}</div>
                            <select name="month" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="">{{ __('All time') }}</option>
                                @foreach($monthOptions as $m)
                                    <option value="{{ $m }}" @selected((string) $month === (string) $m)>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pt-5">
                            <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        </div>
                    </form>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-sm">
                        <div class="p-4 rounded-md bg-gray-50 border border-gray-200">
                            <div class="text-gray-500">{{ __('Total earned') }}</div>
                            <div class="text-lg font-semibold">RM {{ number_format($totalEarnedCents / 100, 2) }}</div>
                        </div>
                        <div class="p-4 rounded-md bg-gray-50 border border-gray-200">
                            <div class="text-gray-500">{{ __('Total paid out') }}</div>
                            <div class="text-lg font-semibold">RM {{ number_format($totalPaidOutCents / 100, 2) }}</div>
                        </div>
                        <div class="p-4 rounded-md bg-gray-50 border border-gray-200">
                            <div class="text-gray-500">{{ __('Pending payout') }}</div>
                            <div class="text-lg font-semibold">RM {{ number_format($pendingPayoutCents / 100, 2) }}</div>
                        </div>
                        <div class="p-4 rounded-md bg-indigo-50 border border-indigo-200">
                            <div class="text-indigo-700">{{ __('Balance (owed to teacher)') }}</div>
                            <div class="text-lg font-semibold text-indigo-900">RM {{ number_format($balanceCents / 100, 2) }}</div>
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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Details') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Earning') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Payout') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Balance') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($entries as $e)
                                    @php($isEarning = $e['type'] === 'earning')
                                    @php($isPayout = $e['type'] === 'payout')
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                            {{ \Illuminate\Support\Carbon::parse($e['at'])->format('Y-m-d') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="font-medium text-gray-800">
                                                {{ $isEarning ? __('Earning') : __('Payout') }}
                                            </div>
                                            <div class="text-xs text-gray-500">{{ $e['status'] ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            <div class="font-medium text-gray-800">{{ $e['label'] }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $e['ref'] ?? '' }}
                                                @if(!empty($e['student']))
                                                    · {{ $e['student'] }}
                                                @endif
                                                @if(!empty($e['plan']))
                                                    · {{ $e['plan'] }}
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-700 whitespace-nowrap">
                                            @if($isEarning)
                                                RM {{ number_format(((int) $e['amount_cents']) / 100, 2) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-700 whitespace-nowrap">
                                            @if($isPayout)
                                                @php($pending = (int) ($e['pending_amount_cents'] ?? 0))
                                                @if($pending > 0)
                                                    <span class="text-gray-500">{{ __('Pending') }}: RM {{ number_format($pending / 100, 2) }}</span>
                                                @else
                                                    RM {{ number_format(abs((int) $e['amount_cents']) / 100, 2) }}
                                                @endif
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 whitespace-nowrap">
                                            RM {{ number_format(((int) $e['balance_cents']) / 100, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No statement entries.') }}</td>
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

