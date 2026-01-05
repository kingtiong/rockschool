<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Change Teacher') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="text-sm text-gray-600">
                        <div><span class="font-medium text-gray-800">{{ __('Student:') }}</span> {{ $lesson->student?->name ?? '—' }}</div>
                        <div><span class="font-medium text-gray-800">{{ __('Current teacher:') }}</span> {{ $lesson->teacher?->name ?? '—' }}</div>
                        <div><span class="font-medium text-gray-800">{{ __('Lesson time:') }}</span> {{ $lesson->scheduled_start_at->format('Y-m-d g:i A') }}</div>
                        <div><span class="font-medium text-gray-800">{{ __('Progress:') }}</span> {{ $lesson->sequence_in_cycle ?? '—' }}/{{ $lesson->cycle_size ?? '—' }}</div>
                    </div>

                    <form method="POST" action="{{ route('management.timetable.lessons.teacher.update', $lesson) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="teacher_id" :value="__('New teacher')" />
                            <select id="teacher_id" name="teacher_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">{{ __('Select teacher') }}</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}" @selected(old('teacher_id', $lesson->teacher_id) == $t->id)>{{ $t->name }} ({{ $t->email }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('teacher_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="reason" :value="__('Reason (optional)')" />
                            <textarea id="reason" name="reason" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <div class="text-sm text-gray-500">
                            {{ __('This changes the teacher for this lesson only. If an unpaid earning record exists, it will be recalculated for the new teacher.') }}
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

