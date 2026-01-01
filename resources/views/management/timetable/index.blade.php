<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Timetable') }}
            </h2>

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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-sm text-gray-600 mb-4">
                        {{ __('Hourly summary: number of classes ongoing (overlapping) in each hour block.') }}
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Hour') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Ongoing classes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($hours as $h)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $h['start']->format('g A') }} – {{ $h['end']->format('g A') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($h['count'] > 0)
                                                <span class="font-medium text-gray-900">{{ $h['count'] }}</span>
                                            @else
                                                <span class="text-gray-400">0</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            {{ __('Teacher clashes: overlapping lessons for the same teacher.') }}
                        </div>
                        <div class="text-sm">
                            @if(count($clashes) > 0)
                                <x-status-badge status="missed" />
                            @else
                                <x-status-badge status="completed" />
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Teacher') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Lesson A') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Lesson B') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($clashes as $c)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">
                                            {{ $c['teacher']?->name ?? __('Unknown') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            <div class="font-medium text-gray-800">
                                                {{ $c['a']->scheduled_start_at->format('g:i A') }} – {{ $c['a']->scheduled_end_at->format('g:i A') }}
                                                <x-status-badge :status="$c['a']->status" class="ms-2" />
                                            </div>
                                            <div class="text-gray-500">{{ __('Student:') }} {{ $c['a']->student?->name ?? '—' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            <div class="font-medium text-gray-800">
                                                {{ $c['b']->scheduled_start_at->format('g:i A') }} – {{ $c['b']->scheduled_end_at->format('g:i A') }}
                                                <x-status-badge :status="$c['b']->status" class="ms-2" />
                                            </div>
                                            <div class="text-gray-500">{{ __('Student:') }} {{ $c['b']->student?->name ?? '—' }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">
                                            {{ __('No teacher clashes found for this date.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-sm text-gray-600 mb-4">{{ __('Lessons (for reference)') }}</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Time') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Teacher') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($lessons as $lesson)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            <div class="font-medium text-gray-800">{{ $lesson->scheduled_start_at->format('g:i A') }} – {{ $lesson->scheduled_end_at->format('g:i A') }}</div>
                                            <div class="text-gray-500">{{ $lesson->minutes }} {{ __('mins') }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $lesson->student?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $lesson->teacher?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm"><x-status-badge :status="$lesson->status" /></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No lessons found for this date.') }}</td>
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

