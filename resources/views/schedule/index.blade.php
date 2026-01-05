<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Schedule') }}
            </h2>
            <div class="text-sm text-gray-500">
                {{ __('Role:') }} <span class="font-medium text-gray-700">{{ auth()->user()->role }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(auth()->user()->role === 'management')
                        <form method="GET" action="{{ route('schedule.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Branch') }}</div>
                                <select name="branch_id" class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    <option value="">{{ __('All branches') }}</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" @selected((string) $selectedBranchId === (string) $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="pt-5">
                                <x-primary-button>{{ __('Filter') }}</x-primary-button>
                            </div>
                        </form>
                    @endif

                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <div class="text-sm text-gray-600">
                            {{ __('Classes are color-coded by status.') }}
                        </div>
                        <div class="flex gap-2">
                            <x-status-badge status="scheduled" />
                            <x-status-badge status="completed" />
                            <x-status-badge status="postponed" />
                            <x-status-badge status="missed" />
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('When') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Duration') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Progress') }}</th>
                                    @if(auth()->user()->role === 'management')
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Branch') }}</th>
                                    @endif
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Teacher') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($lessons as $lesson)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800">
                                            <div class="font-medium">{{ $lesson->scheduled_start_at->format('D, j M Y') }}</div>
                                            <div class="text-gray-500">{{ $lesson->scheduled_start_at->format('g:i A') }} – {{ $lesson->scheduled_end_at->format('g:i A') }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                            {{ $lesson->minutes }} {{ __('mins') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                            @if($lesson->sequence_in_cycle)
                                                {{ $lesson->sequence_in_cycle }}/{{ $lesson->cycle_size }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        @if(auth()->user()->role === 'management')
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                                {{ $lesson->cycle?->enrollment?->branch?->name ?? '—' }}
                                            </td>
                                        @endif
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                            {{ $lesson->student?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                            {{ $lesson->teacher?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <x-status-badge :status="$lesson->status" />
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                            @if(auth()->user()->role === 'teacher' && $lesson->status !== 'completed')
                                                <form method="POST" action="{{ route('schedule.complete', $lesson) }}" class="flex items-center justify-end gap-2">
                                                    @csrf
                                                    <input
                                                        type="text"
                                                        name="remarks"
                                                        placeholder="{{ __('Remarks (optional)') }}"
                                                        class="hidden lg:block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                    />
                                                    <x-primary-button>
                                                        {{ __('Mark completed') }}
                                                    </x-primary-button>
                                                </form>
                                            @elseif(auth()->user()->role === 'student' && in_array($lesson->status, ['scheduled','postponed'], true))
                                                <div class="flex flex-col items-end gap-2">
                                                    <form method="POST" action="{{ route('schedule.absence', $lesson) }}">
                                                        @csrf
                                                        <x-secondary-button>
                                                            {{ __("Can't attend") }}
                                                        </x-secondary-button>
                                                    </form>

                                                    <form method="POST" action="{{ route('schedule.request-change', $lesson) }}" class="flex flex-col items-end gap-2">
                                                        @csrf
                                                        <input
                                                            type="datetime-local"
                                                            name="requested_start_at"
                                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                            required
                                                        />
                                                        <input
                                                            type="text"
                                                            name="reason"
                                                            placeholder="{{ __('Reason (optional)') }}"
                                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                        />
                                                        <x-primary-button>
                                                            {{ __('Request change') }}
                                                        </x-primary-button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->role === 'management' ? 8 : 7 }}" class="px-4 py-8 text-center text-sm text-gray-500">
                                            {{ __('No scheduled classes yet.') }}
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

