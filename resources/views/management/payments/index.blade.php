<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900">
                    <div class="text-sm text-gray-600 mb-4">{{ __('Invoices (cycles awaiting payment)') }}</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Plan') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Cycle') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($invoices as $cycle)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $cycle->enrollment?->student?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $cycle->enrollment?->feePlan?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">#{{ $cycle->cycle_number }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">RM {{ number_format($cycle->cycle_fee_cents / 100, 2) }}</td>
                                        <td class="px-4 py-3 text-sm"><x-status-badge :status="$cycle->status" /></td>
                                        <td class="px-4 py-3 text-right text-sm">
                                            @if($cycle->status !== 'paid')
                                                <form method="POST" action="{{ route('management.payments.cycles.mark-paid', $cycle) }}" class="flex flex-wrap items-center justify-end gap-2">
                                                    @csrf
                                                    <input
                                                        type="datetime-local"
                                                        name="paid_at"
                                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                        value="{{ now()->format('Y-m-d\TH:i') }}"
                                                        required
                                                    />
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        name="amount_rm"
                                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm w-28"
                                                        value="{{ number_format($cycle->cycle_fee_cents / 100, 2, '.', '') }}"
                                                        required
                                                    />
                                                    <select name="method" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                        <option value="bank_transfer">{{ __('Bank transfer') }}</option>
                                                        <option value="cash">{{ __('Cash') }}</option>
                                                        <option value="manual">{{ __('Manual') }}</option>
                                                    </select>
                                                    <input
                                                        name="paid_note"
                                                        type="text"
                                                        class="hidden lg:block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                        placeholder="{{ __('Note (optional)') }}"
                                                    />
                                                    <x-primary-button>{{ __('Mark paid') }}</x-primary-button>
                                                </form>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No invoices.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Plan') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Paid at') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Slip') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($payments as $payment)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $payment->student?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $payment->cycle?->enrollment?->feePlan?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">RM {{ number_format($payment->amount_cents / 100, 2) }}</td>
                                        <td class="px-4 py-3 text-sm"><x-status-badge :status="$payment->status" /></td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $payment->paid_at ? $payment->paid_at->format('Y-m-d g:i A') : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            @if($payment->attachments->count() > 0)
                                                {{ $payment->attachments->last()->original_name }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm">
                                            @if($payment->status === 'pending_review')
                                                <div class="flex justify-end gap-2">
                                                    <form method="POST" action="{{ route('management.payments.approve', $payment) }}">
                                                        @csrf
                                                        <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                                    </form>
                                                    <form method="POST" action="{{ route('management.payments.reject', $payment) }}" class="flex items-center gap-2">
                                                        @csrf
                                                        <input name="review_note" type="text" class="hidden lg:block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="{{ __('Reason (optional)') }}" />
                                                        <x-danger-button>{{ __('Reject') }}</x-danger-button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No payments yet.') }}</td>
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

