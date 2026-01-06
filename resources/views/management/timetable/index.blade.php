<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Timetable') }}
            </h2>

            <div class="flex items-center gap-3">
                <a
                    class="underline text-sm text-indigo-600 hover:text-indigo-900"
                    href="{{ route('management.timetable.index', ['date' => $weekStart->copy()->subWeeks(8)->toDateString(), 'day' => $selectedDayName, 'branch_id' => $selectedBranch?->id]) }}"
                >
                    {{ __('Prev 8 weeks') }}
                </a>
                <a
                    class="underline text-sm text-indigo-600 hover:text-indigo-900"
                    href="{{ route('management.timetable.index', ['date' => $weekStart->copy()->addWeeks(8)->toDateString(), 'day' => $selectedDayName, 'branch_id' => $selectedBranch?->id]) }}"
                >
                    {{ __('Next 8 weeks') }}
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
            <form method="GET" action="{{ route('management.timetable.index') }}" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="date" value="{{ $weekStart->toDateString() }}" />
                <div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Branch') }}</div>
                    <select name="branch_id" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected((string) $selectedBranch?->id === (string) $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Day') }}</div>
                    <select name="day" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        <option value="all" @selected($selectedDayName === 'all')>{{ __('All days') }}</option>
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

            @if($selectedBranch)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-800">{{ __('Rooms') }}</div>
                                <div class="text-xs text-gray-500">{{ __('Add or delete rooms for this branch. (No student details here.)') }}</div>
                            </div>
                            <form method="POST" action="{{ route('management.branches.rooms.store', $selectedBranch) }}" class="flex flex-wrap items-end gap-2">
                                @csrf
                                <input type="hidden" name="date" value="{{ $weekStart->toDateString() }}" />
                                <input type="hidden" name="day" value="{{ $selectedDayName }}" />
                                <div>
                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Room #') }}</div>
                                    <input name="number" type="number" min="1" max="50" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm w-24" required />
                                </div>
                                <div>
                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name (optional)') }}</div>
                                    <input name="name" type="text" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="Piano Room" />
                                </div>
                                <div class="pt-5">
                                    <x-primary-button>{{ __('Add room') }}</x-primary-button>
                                </div>
                            </form>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Room') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($roomModels as $r)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $r->number }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $r->name ?? '—' }}</td>
                                            <td class="px-4 py-3 text-right">
                                                <form method="POST" action="{{ route('management.rooms.destroy', $r) }}" onsubmit="return confirm('{{ __('Delete this room?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="date" value="{{ $weekStart->toDateString() }}" />
                                                    <input type="hidden" name="day" value="{{ $selectedDayName }}" />
                                                    <button type="submit" class="underline text-sm text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No rooms yet. Add one above.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-lg font-semibold text-gray-800">
                                @if($selectedDayName === 'all')
                                    {{ __('All days') }}
                                @else
                                    {{ ucfirst($selectedDayName) }}
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">{{ __('8 weeks timetable') }}</div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <a
                                class="underline text-sm text-indigo-600 hover:text-indigo-900"
                                href="{{ route('management.timetable.slots.create', ['date' => $weekStart->toDateString(), 'branch_id' => $selectedBranch?->id]) }}"
                            >
                                {{ __('Add slot') }}
                            </a>
                            @if($firstLessonTimeKey)
                                <a id="jump-to-first" class="underline text-sm text-indigo-600 hover:text-indigo-900" href="#time-{{ str_replace(':','-',$firstLessonTimeKey) }}">
                                    {{ __('Jump to first class time') }}
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ __('Showing time slots 8:00–22:00 (30 minutes). If you don’t see classes, click “Jump to first class time”.') }}
                    </div>

                    @php
                        $slotTotal = $slotTotal ?? 28;
                        $slotMinutes = $slotMinutes ?? 30;
                        $fmt = function (\Illuminate\Support\Carbon $c): string {
                            $h = (int) $c->format('G'); // 0-23 without leading zero
                            $m = (int) $c->format('i');
                            if ($m === 0) return (string) $h;
                            return $h.'.'.str_pad((string) $m, 2, '0', STR_PAD_LEFT);
                        };
                    @endphp

                    <div id="timetable-grid" class="overflow-auto border border-gray-200 rounded-md" style="max-height: 75vh; min-height: 520px;">
                        @php($minWidthPx = 90 + (count($dates) * count($rooms) * 160))
                        <div style="min-width: {{ max(900, $minWidthPx) }}px;">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="border border-gray-200 px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase w-[110px] sticky top-0 left-0 z-30 bg-gray-50">
                                            {{ __('Time') }}
                                        </th>
                                        @foreach($dates as $d)
                                            <th class="border border-gray-200 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase sticky top-0 z-20 bg-gray-50" colspan="{{ count($rooms) }}">
                                                {{ $d->format('j/n/Y') }}
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <th class="border border-gray-200 px-2 py-2 sticky top-8 left-0 z-30 bg-gray-50"></th>
                                        @foreach($dates as $d)
                                            @foreach($rooms as $room)
                                                <th class="border border-gray-200 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase sticky top-8 z-20 bg-gray-50">
                                                    {{ __('ROOM') }} {{ $room }}
                                                </th>
                                            @endforeach
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($i = 0; $i < $slotTotal; $i++)
                                        @php($t = $gridStart->copy()->addMinutes($i * $slotMinutes))
                                        @php($timeKey = $t->format('H:i'))
                                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50' }}">
                                            <td id="time-{{ str_replace(':','-',$timeKey) }}" class="border border-gray-200 px-2 py-2 text-sm font-medium text-gray-900 whitespace-nowrap sticky left-0 z-10 bg-white">
                                                {{ $fmt($t) }}-{{ $fmt($t->copy()->addMinutes($slotMinutes)) }}
                                            </td>
                                            @foreach($dates as $d)
                                                @php($dateKey = $d->toDateString())
                                                @foreach($rooms as $room)
                                                    @php($lesson = $grid[$dateKey][$timeKey][$room] ?? null)
                                                    <td class="border border-gray-200 px-2 py-2 align-top min-w-[140px]">
                                                        @if($lesson)
                                                            <div class="text-sm font-medium text-gray-900">{{ $lesson->student?->name ?? '—' }}</div>
                                                            <div class="text-xs text-gray-700">{{ $lesson->teacher?->name ?? '—' }}</div>
                                                            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                                                                <a class="underline text-xs text-indigo-600 hover:text-indigo-900" href="{{ route('management.timetable.lessons.reschedule.edit', $lesson) }}">{{ __('Reschedule') }}</a>
                                                                <a class="underline text-xs text-indigo-600 hover:text-indigo-900" href="{{ route('management.timetable.lessons.teacher.edit', $lesson) }}">{{ __('Change teacher') }}</a>
                                                                <form method="POST" action="{{ route('management.timetable.lessons.postpone', $lesson) }}" onsubmit="return confirm('{{ __('Postpone this lesson and shift the rest of the cycle by 1 week?') }}')">
                                                                    @csrf
                                                                    <button type="submit" class="underline text-xs text-red-600 hover:text-red-800">{{ __('Postpone') }}</button>
                                                                </form>
                                                            </div>
                                                        @else
                                                            <div class="text-xs text-gray-400 select-none">{{ __('Available') }}</div>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            @endforeach
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
  // Auto-scroll to first class time, and make jump link scroll inside the grid.
  document.addEventListener('DOMContentLoaded', function () {
    var grid = document.getElementById('timetable-grid');
    if (!grid) return;

    var link = document.getElementById('jump-to-first');
    var hash = link ? link.getAttribute('href') : null;
    if (hash && hash.charAt(0) === '#') {
      var target = document.querySelector(hash);
      if (target) {
        // Scroll to first lesson row on load.
        grid.scrollTop = Math.max(0, target.offsetTop - 80);
      }

      link.addEventListener('click', function (e) {
        var t = document.querySelector(hash);
        if (!t) return;
        e.preventDefault();
        grid.scrollTop = Math.max(0, t.offsetTop - 80);
      });
    }
  });
</script>

