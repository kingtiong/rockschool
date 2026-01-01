<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Schedule 4 Lessons') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="text-sm text-gray-600">
                        <div><span class="font-medium text-gray-800">{{ __('Student:') }}</span> {{ $enrollment->student?->name }}</div>
                        <div><span class="font-medium text-gray-800">{{ __('Teacher:') }}</span> {{ $enrollment->teacher?->name ?? '—' }}</div>
                        <div><span class="font-medium text-gray-800">{{ __('Plan:') }}</span> {{ $enrollment->feePlan?->name }}</div>
                        <div><span class="font-medium text-gray-800">{{ __('Minutes/lesson:') }}</span> {{ $enrollment->minutes_per_lesson }}</div>
                    </div>

                    <form method="POST" action="{{ route('management.cycles.store', $enrollment) }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="start_at" :value="__('First lesson start (date & time)')" />
                            <x-text-input id="start_at" name="start_at" type="datetime-local" class="block mt-1 w-full" :value="old('start_at')" required />
                            <div class="mt-2 text-sm text-gray-500">
                                {{ __('System will auto-create 4 weekly lessons at the same day/time.') }}
                            </div>
                            <x-input-error :messages="$errors->get('start_at')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('management.enrollments.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900">{{ __('Back') }}</a>
                            <x-primary-button>{{ __('Create cycle') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

