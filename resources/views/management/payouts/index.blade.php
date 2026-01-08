<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Teacher Payouts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-sm text-gray-600 mb-4">{{ __('Create a payout from all unpaid earnings.') }}</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Teacher') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Unpaid total') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($teachers as $teacher)
                                    @php $unpaid = (int) ($unpaidTotals[$teacher->id] ?? 0); @endphp
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">
                                            <div>{{ $teacher->name }}</div>
                                            <div class="text-xs">
                                                <a class="underline text-indigo-600 hover:text-indigo-900" href="{{ route('management.teachers.statement', $teacher) }}">{{ __('Full statement') }}</a>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">RM {{ number_format($unpaid / 100, 2) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            @if($unpaid > 0)
                                                <form method="POST" action="{{ route('management.payouts.pay-all', $teacher) }}">
                                                    @csrf
                                                    <x-primary-button>{{ __('Create payout') }}</x-primary-button>
                                                </form>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-sm text-gray-600 mb-4">{{ __('Payout history') }}</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Teacher') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Reference') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($payouts as $payout)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $payout->teacher?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">RM {{ number_format($payout->amount_cents / 100, 2) }}</td>
                                        <td class="px-4 py-3 text-sm"><x-status-badge :status="$payout->status" /></td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $payout->reference ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right text-sm">
                                            <div class="flex justify-end items-center gap-3">
                                                <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.payouts.show', $payout) }}">{{ __('Statement') }}</a>
                                                @if($payout->status === 'pending')
                                                    <form method="POST" action="{{ route('management.payouts.mark-paid', $payout) }}" class="flex justify-end items-center gap-2">
                                                        @csrf
                                                        <input name="reference" type="text" class="hidden lg:block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="{{ __('Reference (optional)') }}" />
                                                        <x-primary-button>{{ __('Mark paid') }}</x-primary-button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No payouts yet.') }}</td>
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

