<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reschedule Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Lesson') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Requested') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($requests as $r)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            @if($r->lesson)
                                                <div class="font-medium text-gray-800">{{ $r->lesson->scheduled_start_at->format('D, j M Y g:i A') }}</div>
                                                <div class="text-gray-500">{{ $r->lesson->minutes }} {{ __('mins') }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $r->lesson?->student?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $r->type }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $r->requested_start_at ? $r->requested_start_at->format('D, j M Y g:i A') : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm"><x-status-badge :status="$r->status" /></td>
                                        <td class="px-4 py-3 text-right text-sm">
                                            @if($r->status === 'pending')
                                                <div class="flex justify-end gap-2">
                                                    <form method="POST" action="{{ route('management.reschedule-requests.approve', $r) }}">
                                                        @csrf
                                                        <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                                    </form>
                                                    <form method="POST" action="{{ route('management.reschedule-requests.reject', $r) }}" class="flex items-center gap-2">
                                                        @csrf
                                                        <input name="decision_note" type="text" class="hidden lg:block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="{{ __('Reason (optional)') }}" />
                                                        <x-danger-button>{{ __('Reject') }}</x-danger-button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No requests yet.') }}</td>
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

