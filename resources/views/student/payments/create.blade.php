<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload Payment Slip') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="text-sm text-gray-600">
                        <div><span class="font-medium text-gray-800">{{ __('Plan:') }}</span> {{ $cycle->enrollment?->feePlan?->name ?? '—' }}</div>
                        <div><span class="font-medium text-gray-800">{{ __('Cycle:') }}</span> #{{ $cycle->cycle_number }}</div>
                        @php($chargesCents = (int) ($cycle->additionalCharges?->sum('amount_cents') ?? 0))
                        @php($totalCents = (int) $cycle->cycle_fee_cents + $chargesCents)
                        <div><span class="font-medium text-gray-800">{{ __('Amount:') }}</span> RM {{ number_format($totalCents / 100, 2) }}</div>
                        @if($chargesCents > 0)
                            <div class="text-xs text-gray-500">
                                {{ __('Base') }}: RM {{ number_format($cycle->cycle_fee_cents / 100, 2) }}
                                · {{ __('Charges') }}: RM {{ number_format($chargesCents / 100, 2) }}
                            </div>
                            <div class="text-xs text-gray-500">
                                @foreach($cycle->additionalCharges as $ch)
                                    <div>- {{ $ch->description }} (RM {{ number_format($ch->amount_cents / 100, 2) }})</div>
                                @endforeach
                            </div>
                        @endif
                        <div><span class="font-medium text-gray-800">{{ __('Status:') }}</span> <x-status-badge :status="$cycle->status" /></div>
                    </div>

                    <form method="POST" action="{{ route('student.cycles.payment.store', $cycle) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="bank_reference" :value="__('Bank reference (optional)')" />
                            <x-text-input id="bank_reference" name="bank_reference" class="block mt-1 w-full" :value="old('bank_reference')" />
                            <x-input-error :messages="$errors->get('bank_reference')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="slip" :value="__('Bank-in slip (image/PDF)')" />
                            <input id="slip" name="slip" type="file" class="block mt-2 w-full text-sm text-gray-700" required />
                            <div class="mt-2 text-sm text-gray-500">{{ __('Max 5MB.') }}</div>
                            <x-input-error :messages="$errors->get('slip')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('student.cycles.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900">{{ __('Back') }}</a>
                            <x-primary-button>{{ __('Submit') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

