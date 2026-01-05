<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reschedule Lesson') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="text-sm text-gray-600">
                        <div><span class="font-medium text-gray-800">{{ __('Student:') }}</span> {{ $lesson->student?->name ?? '—' }}</div>
                        <div><span class="font-medium text-gray-800">{{ __('Teacher:') }}</span> {{ $lesson->teacher?->name ?? '—' }}</div>
                        <div><span class="font-medium text-gray-800">{{ __('Current time:') }}</span> {{ $lesson->scheduled_start_at->format('Y-m-d g:i A') }}</div>
                        <div><span class="font-medium text-gray-800">{{ __('Progress:') }}</span> {{ $lesson->sequence_in_cycle ?? '—' }}/{{ $lesson->cycle_size ?? '—' }}</div>
                    </div>

                    <form method="POST" action="{{ route('management.timetable.lessons.reschedule.update', $lesson) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="requested_start_at" :value="__('New date & time')" />
                            <x-text-input
                                id="requested_start_at"
                                name="requested_start_at"
                                type="datetime-local"
                                class="block mt-1 w-full"
                                :value="old('requested_start_at', $lesson->scheduled_start_at->format('Y-m-d\TH:i'))"
                                required
                            />
                            <x-input-error :messages="$errors->get('requested_start_at')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="reason" :value="__('Reason (optional)')" />
                            <textarea id="reason" name="reason" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <div class="text-sm text-gray-500">
                            {{ __('Reschedule changes only this lesson. Following weeks remain unchanged.') }}
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('management.timetable.index', ['date' => $lesson->scheduled_start_at->toDateString()]) }}" class="text-sm text-gray-600 underline hover:text-gray-900">{{ __('Back') }}</a>
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

