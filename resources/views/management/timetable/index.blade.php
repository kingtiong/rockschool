<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Timetable') }}
            </h2>

            <div class="flex items-center gap-3">
                <a
                    class="underline text-sm text-indigo-600 hover:text-indigo-900"
                    href="{{ route('management.timetable.index', ['date' => $weekStart->copy()->subDays(7)->toDateString()]) }}"
                >
                    {{ __('Prev week') }}
                </a>
                <a
                    class="underline text-sm text-indigo-600 hover:text-indigo-900"
                    href="{{ route('management.timetable.index', ['date' => $weekStart->copy()->addDays(7)->toDateString()]) }}"
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

            <div class="flex flex-wrap items-center gap-3">
                @foreach($days as $d)
                    @php($dayKey = strtolower($d['date']->format('l')))
                    <a
                        class="px-3 py-1 rounded-full text-sm {{ $selectedDayName === $dayKey ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                        href="{{ route('management.timetable.index', ['date' => $weekStart->toDateString(), 'day' => $dayKey]) }}"
                    >
                        {{ $d['date']->format('D') }}
                    </a>
                @endforeach
            </div>

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
                        <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.timetable.slots.create', ['date' => $day['date']->toDateString()]) }}">
                            {{ __('Add slot') }}
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Time') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Teacher') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Progress') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($day['lessons'] as $lesson)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                            <div class="font-medium text-gray-800">{{ $lesson->scheduled_start_at->format('g:i A') }} – {{ $lesson->scheduled_end_at->format('g:i A') }}</div>
                                            <div class="text-gray-500">{{ $lesson->minutes }} {{ __('mins') }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium whitespace-nowrap">{{ $lesson->student?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $lesson->teacher?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                            {{ $lesson->sequence_in_cycle ?? '—' }}/{{ $lesson->cycle_size ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm whitespace-nowrap"><x-status-badge :status="$lesson->status" /></td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-3">
                                                <form method="POST" action="{{ route('management.timetable.lessons.postpone', $lesson) }}" onsubmit="return confirm('{{ __('Postpone this lesson and push the remaining lessons to next week?') }}')">
                                                    @csrf
                                                    <button type="submit" class="underline text-sm text-amber-700 hover:text-amber-900">
                                                        {{ __('Postpone') }}
                                                    </button>
                                                </form>
                                                <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.timetable.lessons.reschedule.edit', $lesson) }}">
                                                    {{ __('Reschedule') }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                                            {{ __('No lessons.') }}
                                        </td>
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

