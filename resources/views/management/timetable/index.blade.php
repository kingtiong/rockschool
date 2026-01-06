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
            <div class="text-sm text-gray-600">
                @if($selectedDayName === 'all')
                    <span class="font-medium text-gray-800">{{ __('All days') }}</span>
                @else
                    <span class="font-medium text-gray-800">{{ ucfirst($selectedDayName) }}</span>
                @endif
                {{ __('(8 weeks):') }}
                <span class="font-medium text-gray-800">{{ $weekStart->toDateString() }}</span>
                {{ __('to') }}
                <span class="font-medium text-gray-800">{{ $weekStart->copy()->addWeeks(8)->subDay()->toDateString() }}</span>
            </div>

            <div class="text-sm text-gray-600 flex flex-wrap items-center gap-4">
                <div>
                    <span class="font-medium text-gray-800">{{ __('Classes found:') }}</span> {{ $lessonsCount }}
                </div>
                @if($firstLesson && $firstLesson->scheduled_start_at)
                    <div>
                        <span class="font-medium text-gray-800">{{ __('First class:') }}</span>
                        {{ $firstLesson->scheduled_start_at->format('Y-m-d g:i A') }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ __('First cell match:') }}
                        {{ $firstCellHit ? __('YES') : __('NO') }}
                        @if($firstLessonDateKey && $firstLessonTimeKey && $firstLessonRoom)
                            ({{ $firstLessonDateKey }} {{ $firstLessonTimeKey }} · {{ __('Room') }} {{ $firstLessonRoom }})
                        @endif
                    </div>
                    @if($firstLessonTimeKey)
                        <a id="jump-to-first" class="underline text-sm text-indigo-600 hover:text-indigo-900" href="#time-{{ str_replace(':','-',$firstLessonTimeKey) }}">
                            {{ __('Jump to first class time') }}
                        </a>
                    @endif
                @endif
            </div>
            @if($lessonsCount > 0)
                <div class="text-xs text-gray-500">
                    {{ __('Tip: your first class is at 2:00 PM — click “Jump to first class time” or scroll inside the grid box down to 2:00 PM.') }}
                </div>
            @endif

            <div class="text-xs text-gray-500">
                <span class="font-medium text-gray-700">{{ __('Debug:') }}</span>
                {{ __('slots=') }}{{ ($slotCount ?? 0) + 1 }},
                {{ __('slotMinutes=') }}{{ $slotMinutes ?? 30 }},
                {{ __('grid=') }}{{ isset($gridStart) ? $gridStart->format('H:i') : '—' }}–{{ isset($gridEnd) ? $gridEnd->format('H:i') : '—' }},
                {{ __('dates=') }}{{ isset($dates) ? count($dates) : 0 }},
                {{ __('rooms=') }}{{ isset($rooms) ? count($rooms) : 0 }}
            </div>

            <form method="GET" action="{{ route('management.timetable.index') }}" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="date" value="{{ $weekStart->toDateString() }}" />
                <div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Branch') }}</div>
                    <select name="branch_id" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        <option value="">{{ __('All branches') }}</option>
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
                            <div class="text-sm text-gray-500">
                                {{ __('Scroll or drag right to see all weeks. Scroll down to see times.') }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ __('Weeks shown:') }} {{ count($dates) }} · {{ __('Rooms:') }} {{ count($rooms) }}
                            </div>
                        </div>
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

                    <div class="overflow-auto border border-gray-200 rounded-md" style="max-height: 70vh; min-height: 420px;">
                        @php($minWidthPx = 90 + (count($dates) * count($rooms) * 160))
                        <div style="min-width: {{ max(900, $minWidthPx) }}px;">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="border border-gray-200 px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase w-[90px]">
                                            {{ __('Time') }}
                                        </th>
                                        @foreach($dates as $d)
                                            <th class="border border-gray-200 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase" colspan="{{ count($rooms) }}">
                                                {{ $d->format('j/n/Y') }}
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <th class="border border-gray-200 px-2 py-2"></th>
                                        @foreach($dates as $d)
                                            @foreach($rooms as $room)
                                                <th class="border border-gray-200 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">
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
                                        <tr>
                                            <td class="border border-gray-200 px-2 py-2 text-sm text-gray-800 whitespace-nowrap">
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

                    <div class="mt-6">
                        <div class="text-sm font-medium text-gray-800 mb-2">{{ __('Lessons in this view (sanity check)') }}</div>
                        <div class="text-xs text-gray-500 mb-3">
                            {{ __('If you see rows here but not in the grid, it means the grid is not rendering/scrolling properly in the browser.') }}
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date/time') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Room') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Student') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Teacher') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($lessonsPreview as $l)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-700 whitespace-nowrap">{{ $l->scheduled_start_at?->format('Y-m-d g:i A') ?? '—' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 whitespace-nowrap">{{ $l->classroom_number ?? 1 }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 whitespace-nowrap">{{ $l->student?->name ?? '—' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 whitespace-nowrap">{{ $l->teacher?->name ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No lessons in this range.') }}</td>
                                        </tr>
                                    @endforelse
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
  // Ensure "Jump to first class time" scrolls inside the grid box.
  document.addEventListener('DOMContentLoaded', function () {
    var link = document.getElementById('jump-to-first');
    var grid = document.getElementById('timetable-grid');
    if (!grid) return;

    // Auto-scroll to first class time on load.
    var jump = grid.getAttribute('data-jump-target');
    if (jump && jump.charAt(0) === '#') {
      var row0 = document.querySelector(jump);
      if (row0) {
        grid.scrollTop = Math.max(0, row0.offsetTop - 80);
      }
    }

    if (link) {
      link.addEventListener('click', function (e) {
        var hash = link.getAttribute('href');
        if (!hash || hash.charAt(0) !== '#') return;
        var row = document.querySelector(hash);
        if (!row) return;
        e.preventDefault();
        // Scroll the grid so the row is visible near the top.
        var top = row.offsetTop;
        grid.scrollTop = Math.max(0, top - 80);
      });
    }

    // Drag-to-scroll for large timetable (desktop mouse).
    var isDown = false;
    var startX = 0;
    var startY = 0;
    var scrollLeft = 0;
    var scrollTop = 0;

    grid.addEventListener('mousedown', function (e) {
      // Ignore dragging when interacting with controls.
      if (e.target.closest('a,button,input,select,textarea,form')) return;
      isDown = true;
      grid.style.cursor = 'grabbing';
      startX = e.pageX;
      startY = e.pageY;
      scrollLeft = grid.scrollLeft;
      scrollTop = grid.scrollTop;
      e.preventDefault();
    });

    window.addEventListener('mouseup', function () {
      if (!isDown) return;
      isDown = false;
      grid.style.cursor = 'grab';
    });

    grid.addEventListener('mouseleave', function () {
      if (!isDown) return;
      isDown = false;
      grid.style.cursor = 'grab';
    });

    grid.addEventListener('mousemove', function (e) {
      if (!isDown) return;
      var dx = e.pageX - startX;
      var dy = e.pageY - startY;
      grid.scrollLeft = scrollLeft - dx;
      grid.scrollTop = scrollTop - dy;
    });
  });
</script>

