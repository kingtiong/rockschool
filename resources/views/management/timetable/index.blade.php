<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Timetable') }}
            </h2>

            <div class="flex items-center gap-3">
                <a
                    class="underline text-sm text-indigo-600 hover:text-indigo-900"
                    href="{{ route('management.timetable.index', ['date' => $weekStart->copy()->subDays(7)->toDateString(), 'day' => $selectedDayName, 'branch_id' => $selectedBranch?->id]) }}"
                >
                    {{ __('Prev week') }}
                </a>
                <a
                    class="underline text-sm text-indigo-600 hover:text-indigo-900"
                    href="{{ route('management.timetable.index', ['date' => $weekStart->copy()->addDays(7)->toDateString(), 'day' => $selectedDayName, 'branch_id' => $selectedBranch?->id]) }}"
                >
                    {{ __('Next week') }}
                </a>
            </div>

            <form method="GET" action="{{ route('management.timetable.index') }}" class="flex items-center gap-2">
                <input
                    type="date"
                    name="date"
                    value="{{ $date->toDateString() }}"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                />
                <input type="hidden" name="day" value="{{ $selectedDayName }}" />
                @if($selectedBranch)
                    <input type="hidden" name="branch_id" value="{{ $selectedBranch->id }}" />
                @endif
                <x-primary-button>{{ __('Go') }}</x-primary-button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="text-sm text-gray-600">
                {{ __('Week:') }} <span class="font-medium text-gray-800">{{ $weekStart->toDateString() }}</span>
                {{ __('to') }}
                <span class="font-medium text-gray-800">{{ $weekEnd->copy()->subDay()->toDateString() }}</span>
            </div>

            <form method="GET" action="{{ route('management.timetable.index') }}" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="date" value="{{ $weekStart->toDateString() }}" />
                <div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Branch') }}</div>
                    <select name="branch_id" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected($selectedBranch?->id === $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Day') }}</div>
                    <select name="day" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        <option value="monday" @selected($selectedDayName === 'monday')>{{ __('Monday') }}</option>
                        <option value="tuesday" @selected($selectedDayName === 'tuesday')>{{ __('Tuesday') }}</option>
                        <option value="wednesday" @selected($selectedDayName === 'wednesday')>{{ __('Wednesday') }}</option>
                        <option value="thursday" @selected($selectedDayName === 'thursday')>{{ __('Thursday') }}</option>
                        <option value="friday" @selected($selectedDayName === 'friday')>{{ __('Friday') }}</option>
                        <option value="saturday" @selected($selectedDayName === 'saturday')>{{ __('Saturday') }}</option>
                        <option value="sunday" @selected($selectedDayName === 'sunday')>{{ __('Sunday') }}</option>
                    </select>
                </div>
                <div class="pt-5">
                    <x-primary-button>{{ __('Change') }}</x-primary-button>
                </div>
            </form>

            @php($day = $selectedDay)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-lg font-semibold text-gray-800">
                                {{ $day['date']->format('l') }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $day['date']->toDateString() }}
                            </div>
                        </div>
                        <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.timetable.slots.create', ['date' => $day['date']->toDateString(), 'branch_id' => $selectedBranch?->id]) }}">
                            {{ __('Add slot') }}
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <div class="min-w-[900px]">
                            <div class="grid gap-px bg-gray-200" style="grid-template-columns: 90px repeat({{ count($rooms) }}, minmax(180px, 1fr));">
                                <div class="bg-white px-2 py-2 text-xs font-medium text-gray-500 uppercase">{{ __('Time') }}</div>
                                @foreach($rooms as $room)
                                    <div class="bg-white px-3 py-2 text-xs font-medium text-gray-500 uppercase">{{ __('Room') }} {{ $room }}</div>
                                @endforeach

                                @foreach($timeSlots as $t)
                                    <div class="bg-white px-2 py-2 text-xs text-gray-600 whitespace-nowrap">
                                        {{ $t->format('g:i A') }}
                                    </div>
                                    @foreach($rooms as $room)
                                        @php($lesson = $grid[$t->format('H:i')][$room] ?? null)
                                        <div class="bg-white px-2 py-2 min-h-[44px]">
                                            @if($lesson)
                                                <div class="rounded-md border border-gray-200 p-2 bg-indigo-50">
                                                    <div class="text-sm font-medium text-gray-900">{{ $lesson->student?->name ?? '—' }}</div>
                                                    <div class="text-xs text-gray-700">{{ $lesson->teacher?->name ?? '—' }}</div>
                                                    <div class="text-xs text-gray-600">
                                                        {{ $lesson->minutes }}{{ __('m') }} · {{ $lesson->sequence_in_cycle ?? '—' }}/{{ $lesson->cycle_size ?? '—' }}
                                                    </div>
                                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                                        <x-status-badge :status="$lesson->status" />
                                                        <form method="POST" action="{{ route('management.timetable.lessons.postpone', $lesson) }}" onsubmit="return confirm('{{ __('Postpone this lesson and push the remaining lessons to next week?') }}')">
                                                            @csrf
                                                            <button type="submit" class="underline text-xs text-amber-700 hover:text-amber-900">{{ __('Postpone') }}</button>
                                                        </form>
                                                        <a class="underline text-xs text-indigo-700 hover:text-indigo-900" href="{{ route('management.timetable.lessons.teacher.edit', $lesson) }}">{{ __('Teacher') }}</a>
                                                        <a class="underline text-xs text-indigo-700 hover:text-indigo-900" href="{{ route('management.timetable.lessons.reschedule.edit', $lesson) }}">{{ __('Reschedule') }}</a>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

