<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Fee Plan') }}: {{ $feePlan->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('management.fee-plans.update', $feePlan) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $feePlan->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="cycle_fee_rm" :value="__('Cycle fee (RM)')" />
                            <x-text-input id="cycle_fee_rm" name="cycle_fee_rm" class="block mt-1 w-full" type="number" step="0.01" :value="old('cycle_fee_rm', number_format($feePlan->cycle_fee_cents / 100, 2, '.', ''))" required />
                            <x-input-error :messages="$errors->get('cycle_fee_rm')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="lessons_per_cycle" :value="__('Lessons per cycle')" />
                                <x-text-input id="lessons_per_cycle" name="lessons_per_cycle" class="block mt-1 w-full" type="number" :value="old('lessons_per_cycle', $feePlan->lessons_per_cycle)" required />
                                <x-input-error :messages="$errors->get('lessons_per_cycle')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="minutes_per_lesson_default" :value="__('Minutes per lesson (default)')" />
                                <x-text-input id="minutes_per_lesson_default" name="minutes_per_lesson_default" class="block mt-1 w-full" type="number" :value="old('minutes_per_lesson_default', $feePlan->minutes_per_lesson_default)" required />
                                <x-input-error :messages="$errors->get('minutes_per_lesson_default')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="allow_half_hour" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('allow_half_hour', $feePlan->allow_half_hour)) />
                                <span class="text-sm text-gray-700">{{ __('Allow 30-min option') }}</span>
                            </label>

                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('active', $feePlan->active)) />
                                <span class="text-sm text-gray-700">{{ __('Active') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('management.fee-plans.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900">{{ __('Back') }}</a>
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

