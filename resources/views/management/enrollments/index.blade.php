<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Enrollments') }}
            </h2>
            <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.enrollments.create') }}">
                {{ __('Create enrollment') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Teacher') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Plan') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Minutes') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($enrollments as $enrollment)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $enrollment->student?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $enrollment->teacher?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $enrollment->feePlan?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $enrollment->minutes_per_lesson }}</td>
                                        <td class="px-4 py-3 text-sm"><x-status-badge :status="$enrollment->status" /></td>
                                        <td class="px-4 py-3 text-right">
                                            <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.cycles.create', $enrollment) }}">
                                                {{ __('Schedule 4 lessons') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No enrollments yet.') }}</td>
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

