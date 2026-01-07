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

                    <div id="timetable-grid" class="overflow-x-auto border border-gray-200 rounded-md">
                        @php($colCount = 1 + (count($dates) * count($rooms)))
                        @php($gridColsStyle = 'grid-template-columns: 110px repeat('.(count($dates) * count($rooms)).', 160px);')
                        @php($rowH = 36)

                        <div style="min-width: {{ max(900, 110 + (count($dates) * count($rooms) * 160)) }}px;">
                            <div class="px-2 py-2 text-xs text-gray-500 border-b border-gray-200 bg-white">
                                {{ __('Rows:') }} {{ count($timeSlots ?? []) }} · {{ __('Dates:') }} {{ count($dates ?? []) }} · {{ __('Rooms:') }} {{ count($rooms ?? []) }}
                            </div>
                            <!-- Header row: dates -->
                            <div class="grid sticky top-0 z-30 bg-gray-50 border-b border-gray-200" style="{{ $gridColsStyle }}">
                                <div class="border-r border-gray-200 px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase sticky left-0 bg-gray-50">
                                    {{ __('Time') }}
                                </div>
                                @foreach($dates as $d)
                                    @php($dateKey = $d->toDateString())
                                    @php($band = (int) ($weekBandByDate[$dateKey] ?? 0))
                                    @php($colBg = $band === 1 ? 'bg-indigo-50/70' : 'bg-white')
                                    @foreach($rooms as $room)
                                        <div class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase {{ $colBg }}">
                                            @if($loop->first)
                                                <div class="font-medium text-gray-500">{{ $d->format('j/n/Y') }}</div>
                                            @endif
                                            <div class="text-[11px] text-gray-500">{{ __('ROOM') }} {{ $room }}</div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>

                            <!-- Body: time slots -->
                            @foreach(($timeSlots ?? []) as $i => $t)
                                @php($timeKey = $t->format('H:i'))
                                <div class="grid border-b border-gray-100 {{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50' }}" style="{{ $gridColsStyle }}">
                                    <div id="time-{{ str_replace(':','-',$timeKey) }}" class="border-r border-gray-200 px-2 py-2 text-sm font-medium text-gray-900 whitespace-nowrap sticky left-0 bg-white">
                                        {{ $fmt($t) }}-{{ $fmt($t->copy()->addMinutes($slotMinutes)) }}
                                    </div>
                                    @foreach($dates as $d)
                                        @php($dateKey = $d->toDateString())
                                        @php($band = (int) ($weekBandByDate[$dateKey] ?? 0))
                                        @php($colBg = $band === 1 ? 'bg-indigo-50/50' : '')
                                        @foreach($rooms as $room)
                                            @php($lesson = $grid[$dateKey][$timeKey][$room] ?? null)
                                            @php($covered = $covers[$dateKey][$timeKey][$room] ?? false)
                                            @php($span = $spans[$dateKey][$timeKey][$room] ?? 1)
                                            <div class="border-r border-gray-100 px-2 py-1 relative overflow-visible {{ $colBg }} {{ $covered && ! $lesson ? 'pointer-events-none' : '' }}" style="min-height: {{ $rowH }}px;">
                                                @if($lesson)
                                                    @php($payload = [
                                                        'id' => (int) $lesson->id,
                                                        'student' => $lesson->student?->name ?? '—',
                                                        'teacher' => $lesson->teacher?->name ?? '—',
                                                        'teacher_id' => (int) ($lesson->teacher_id ?? 0),
                                                        'time' => (($lesson->scheduled_start_at?->format('g:i A') ?? '').'–'.($lesson->scheduled_end_at?->format('g:i A') ?? '')),
                                                        'scheduled_start_local' => $lesson->scheduled_start_at?->format('Y-m-d\\TH:i') ?? '',
                                                        'reschedule_action' => route('management.timetable.lessons.reschedule.update', $lesson),
                                                        'teacher_action' => route('management.timetable.lessons.teacher.update', $lesson),
                                                        'postpone_action' => route('management.timetable.lessons.postpone', $lesson),
                                                        'span' => (int) $span,
                                                    ])
                                                    @endphp
                                                    <div
                                                        class="js-lesson-card absolute inset-x-1 top-1 rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 overflow-hidden cursor-pointer z-20 pointer-events-auto"
                                                        style="height: calc({{ $rowH }}px * {{ max(1, (int) $span) }} - 6px);"
                                                        data-lesson='@json($payload)'
                                                    >
                                                        <div class="text-[11px] text-indigo-700 font-medium leading-tight">{{ $lesson->scheduled_start_at?->format('g:i A') }}–{{ $lesson->scheduled_end_at?->format('g:i A') }}</div>
                                                        <div class="text-sm font-semibold text-gray-900 leading-tight">{{ $lesson->student?->name ?? '—' }}</div>
                                                        <div class="text-[11px] text-gray-700 leading-tight">{{ $lesson->teacher?->name ?? '—' }}</div>
                                                        <div class="mt-1 text-[11px] text-gray-500">{{ __('Click for actions') }}</div>
                                                    </div>
                                                @elseif(! $covered)
                                                    <div class="text-[11px] text-gray-400 select-none leading-tight">{{ __('Available') }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Actions Modal (plain JS, no Alpine dependency) -->
<div id="lesson-actions-modal" class="fixed inset-0 z-50 hidden">
    <div id="lesson-actions-overlay" class="absolute inset-0 bg-gray-500 opacity-75"></div>
    <div class="relative mx-auto mt-10 w-full max-w-md bg-white rounded-lg shadow-xl overflow-hidden">
        <div class="p-6 space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-lg font-semibold text-gray-900">{{ __('Class actions') }}</div>
                    <div id="lesson-actions-subtitle" class="text-sm text-gray-600"></div>
                </div>
                <button type="button" id="lesson-actions-close" class="text-sm text-gray-500 underline">{{ __('Close') }}</button>
            </div>

            <div id="lesson-actions-menu" class="flex flex-col gap-2">
                <button type="button" id="lesson-actions-btn-reschedule" class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">{{ __('Reschedule') }}</button>
                <button type="button" id="lesson-actions-btn-teacher" class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-900">{{ __('Change teacher (this class only)') }}</button>
                <button type="button" id="lesson-actions-btn-postpone" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">{{ __('Postpone / Cancel') }}</button>
            </div>

            <div id="lesson-actions-reschedule" class="hidden space-y-3">
                <div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('New date/time') }}</div>
                    <input id="lesson-actions-reschedule-at" type="datetime-local" class="mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Reason (optional)') }}</div>
                    <input id="lesson-actions-reschedule-reason" type="text" class="mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                </div>
                <div class="flex items-center justify-between gap-3">
                    <button type="button" class="underline text-sm text-gray-600 hover:text-gray-900" id="lesson-actions-back-1">{{ __('Back') }}</button>
                    <form id="lesson-actions-reschedule-form" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="requested_start_at" id="lesson-actions-reschedule-hidden-at">
                        <input type="hidden" name="reason" id="lesson-actions-reschedule-hidden-reason">
                        <x-primary-button>{{ __('Save reschedule') }}</x-primary-button>
                    </form>
                </div>
            </div>

            <div id="lesson-actions-teacher" class="hidden space-y-3">
                <div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Select teacher') }}</div>
                    <select id="lesson-actions-teacher-id" class="mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                        <option value="">{{ __('Choose teacher') }}</option>
                        @foreach(($teachers ?? collect()) as $t)
                            <option value="{{ (int) $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Reason (optional)') }}</div>
                    <input id="lesson-actions-teacher-reason" type="text" class="mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                </div>
                <div class="flex items-center justify-between gap-3">
                    <button type="button" class="underline text-sm text-gray-600 hover:text-gray-900" id="lesson-actions-back-2">{{ __('Back') }}</button>
                    <form id="lesson-actions-teacher-form" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="teacher_id" id="lesson-actions-teacher-hidden-id">
                        <input type="hidden" name="reason" id="lesson-actions-teacher-hidden-reason">
                        <x-primary-button>{{ __('Save teacher') }}</x-primary-button>
                    </form>
                </div>
            </div>

            <div id="lesson-actions-postpone" class="hidden space-y-4">
                <div class="text-sm text-gray-700">{{ __('This will postpone this lesson and shift the rest of the cycle by 1 week.') }}</div>
                <div class="flex items-center justify-between gap-3">
                    <button type="button" class="underline text-sm text-gray-600 hover:text-gray-900" id="lesson-actions-back-3">{{ __('Back') }}</button>
                    <form id="lesson-actions-postpone-form" method="POST" onsubmit="return confirm('{{ __('Postpone this lesson and shift the rest of the cycle by 1 week?') }}')">
                        @csrf
                        <x-danger-button>{{ __('Confirm postpone') }}</x-danger-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('lesson-actions-modal');
    var overlay = document.getElementById('lesson-actions-overlay');
    var closeBtn = document.getElementById('lesson-actions-close');
    var subtitle = document.getElementById('lesson-actions-subtitle');

    var menu = document.getElementById('lesson-actions-menu');
    var paneReschedule = document.getElementById('lesson-actions-reschedule');
    var paneTeacher = document.getElementById('lesson-actions-teacher');
    var panePostpone = document.getElementById('lesson-actions-postpone');

    var btnReschedule = document.getElementById('lesson-actions-btn-reschedule');
    var btnTeacher = document.getElementById('lesson-actions-btn-teacher');
    var btnPostpone = document.getElementById('lesson-actions-btn-postpone');

    var back1 = document.getElementById('lesson-actions-back-1');
    var back2 = document.getElementById('lesson-actions-back-2');
    var back3 = document.getElementById('lesson-actions-back-3');

    var resAt = document.getElementById('lesson-actions-reschedule-at');
    var resReason = document.getElementById('lesson-actions-reschedule-reason');
    var resForm = document.getElementById('lesson-actions-reschedule-form');
    var resHiddenAt = document.getElementById('lesson-actions-reschedule-hidden-at');
    var resHiddenReason = document.getElementById('lesson-actions-reschedule-hidden-reason');

    var teacherId = document.getElementById('lesson-actions-teacher-id');
    var teacherReason = document.getElementById('lesson-actions-teacher-reason');
    var teacherForm = document.getElementById('lesson-actions-teacher-form');
    var teacherHiddenId = document.getElementById('lesson-actions-teacher-hidden-id');
    var teacherHiddenReason = document.getElementById('lesson-actions-teacher-hidden-reason');

    var postponeForm = document.getElementById('lesson-actions-postpone-form');

    function showPane(which) {
      menu.classList.add('hidden');
      paneReschedule.classList.add('hidden');
      paneTeacher.classList.add('hidden');
      panePostpone.classList.add('hidden');
      if (which === 'menu') menu.classList.remove('hidden');
      if (which === 'reschedule') paneReschedule.classList.remove('hidden');
      if (which === 'teacher') paneTeacher.classList.remove('hidden');
      if (which === 'postpone') panePostpone.classList.remove('hidden');
    }

    function openModal(payload) {
      subtitle.textContent = (payload.time || '') + ' · ' + (payload.student || '') + ' · ' + (payload.teacher || '');
      resForm.action = payload.reschedule_action || '';
      teacherForm.action = payload.teacher_action || '';
      postponeForm.action = payload.postpone_action || '';
      resAt.value = payload.scheduled_start_local || '';
      teacherId.value = payload.teacher_id ? String(payload.teacher_id) : '';
      resReason.value = '';
      teacherReason.value = '';
      showPane('menu');
      modal.classList.remove('hidden');
    }

    function closeModal() {
      modal.classList.add('hidden');
    }

    document.addEventListener('click', function (e) {
      var card = e.target.closest('.js-lesson-card');
      if (!card) return;
      try {
        var payload = JSON.parse(card.getAttribute('data-lesson') || '{}');
        openModal(payload);
      } catch (err) {
        // ignore
      }
    });

    overlay.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);
    window.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeModal();
    });

    btnReschedule.addEventListener('click', function () { showPane('reschedule'); });
    btnTeacher.addEventListener('click', function () { showPane('teacher'); });
    btnPostpone.addEventListener('click', function () { showPane('postpone'); });
    back1.addEventListener('click', function () { showPane('menu'); });
    back2.addEventListener('click', function () { showPane('menu'); });
    back3.addEventListener('click', function () { showPane('menu'); });

    resForm.addEventListener('submit', function () {
      resHiddenAt.value = resAt.value;
      resHiddenReason.value = resReason.value;
    });
    teacherForm.addEventListener('submit', function () {
      teacherHiddenId.value = teacherId.value;
      teacherHiddenReason.value = teacherReason.value;
    });
  });
</script>

