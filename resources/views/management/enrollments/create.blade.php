<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Enrollment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('management.enrollments.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="branch_id" :value="__('Branch (optional)')" />
                            <select id="branch_id" name="branch_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('No branch') }}</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('branch_id')" class="mt-2" />
                        </div>

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
                            <x-input-label for="teacher_id" :value="__('Teacher (optional)')" />
                            <select id="teacher_id" name="teacher_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('Unassigned') }}</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}" @selected(old('teacher_id') == $t->id)>{{ $t->name }} ({{ $t->email }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('teacher_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="fee_plan_id" :value="__('Fee plan')" />
                            <select id="fee_plan_id" name="fee_plan_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">{{ __('Select plan') }}</option>
                                @foreach($feePlans as $p)
                                    <option value="{{ $p->id }}" @selected(old('fee_plan_id') == $p->id)>{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('fee_plan_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="minutes_per_lesson" :value="__('Minutes per lesson')" />
                            <select id="minutes_per_lesson" name="minutes_per_lesson" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="60" @selected(old('minutes_per_lesson', '60') == '60')>60</option>
                                <option value="30" @selected(old('minutes_per_lesson') == '30')>30</option>
                                <option value="45" @selected(old('minutes_per_lesson') == '45')>45</option>
                            </select>
                            <div class="mt-2 text-sm text-gray-500">
                                {{ __('(Grade 7–8 uses 45 mins. Half-hour option is only valid for 60-min plans.)') }}
                            </div>
                            <x-input-error :messages="$errors->get('minutes_per_lesson')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="interval_weeks" :value="__('Monthly frequency')" />
                            <select id="interval_weeks" name="interval_weeks" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="1" @selected(old('interval_weeks', '1') == '1')>{{ __('1 month 4 times (weekly)') }}</option>
                                <option value="2" @selected(old('interval_weeks') == '2')>{{ __('1 month 2 times (every 2 weeks)') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('interval_weeks')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="started_on" :value="__('Started on (optional)')" />
                            <x-text-input id="started_on" name="started_on" type="date" class="block mt-1 w-full" :value="old('started_on')" />
                            <x-input-error :messages="$errors->get('started_on')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="preferred_start_time" :value="__('Preferred time (optional)')" />
                            <x-text-input id="preferred_start_time" name="preferred_start_time" type="time" class="block mt-1 w-full" :value="old('preferred_start_time')" />
                            <x-input-error :messages="$errors->get('preferred_start_time')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('management.enrollments.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900">{{ __('Back') }}</a>
                            <x-primary-button>{{ __('Create') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

