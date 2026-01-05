<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Student Slot') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('management.timetable.slots.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="student_id" :value="__('Student')" />
                            <select id="student_id" name="student_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">{{ __('Select student') }}</option>
                                @foreach($students as $s)
                                    <option value="{{ $s->id }}" @selected(old('student_id') == $s->id)>{{ $s->name }} ({{ $s->email }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="teacher_id" :value="__('Teacher')" />
                            <select id="teacher_id" name="teacher_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('Unassigned') }}</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}" @selected(old('teacher_id') == $t->id)>{{ $t->name }} ({{ $t->email }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('teacher_id')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="start_date" :value="__('Start date')" />
                                <x-text-input id="start_date" name="start_date" type="date" class="block mt-1 w-full" :value="old('start_date', $date->toDateString())" required />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="start_time" :value="__('Time')" />
                                <x-text-input id="start_time" name="start_time" type="time" class="block mt-1 w-full" :value="old('start_time', '14:00')" required />
                                <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="minutes_per_lesson" :value="__('Timeframe (minutes)')" />
                                <select id="minutes_per_lesson" name="minutes_per_lesson" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="30" @selected(old('minutes_per_lesson') == '30')>30</option>
                                    <option value="45" @selected(old('minutes_per_lesson') == '45')>45</option>
                                    <option value="60" @selected(old('minutes_per_lesson', '60') == '60')>60</option>
                                </select>
                                <x-input-error :messages="$errors->get('minutes_per_lesson')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="interval_weeks" :value="__('Repeat')" />
                                <select id="interval_weeks" name="interval_weeks" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="1" @selected(old('interval_weeks', '1') == '1')>{{ __('Weekly') }}</option>
                                    <option value="2" @selected(old('interval_weeks') == '2')>{{ __('Every 2 weeks') }}</option>
                                </select>
                                <x-input-error :messages="$errors->get('interval_weeks')" class="mt-2" />
                            </div>
                        </div>

                        <div class="text-sm text-gray-500">
                            {{ __('This will create a new 4-lesson cycle starting at the selected date/time, repeating weekly (or every 2 weeks). Student must already have an active enrollment.') }}
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('management.timetable.index', ['date' => $date->toDateString()]) }}" class="text-sm text-gray-600 underline hover:text-gray-900">{{ __('Back') }}</a>
                            <x-primary-button>{{ __('Create slot') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

