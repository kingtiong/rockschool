<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Fees & Cycles') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Plan') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Cycle') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Invoice') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($cycles as $cycle)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">
                                            {{ $cycle->enrollment?->feePlan?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            #{{ $cycle->cycle_number }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            @php($chargesCents = (int) ($cycle->additionalCharges?->sum('amount_cents') ?? 0))
                                            @php($totalCents = (int) $cycle->cycle_fee_cents + $chargesCents)
                                            RM {{ number_format($totalCents / 100, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <x-status-badge :status="$cycle->status" />
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            @if($cycle->invoice)
                                                <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('invoices.show', $cycle->invoice) }}">
                                                    {{ $cycle->invoice->invoice_number }}
                                                </a>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm">
                                            @if(in_array($cycle->status, ['awaiting_student_payment','payment_submitted'], true))
                                                <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('student.cycles.payment.create', $cycle) }}">
                                                    {{ __('Upload payment slip') }}
                                                </a>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No cycles yet.') }}</td>
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

