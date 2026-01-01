<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Fee Plans') }}
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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Cycle fee') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Lessons/cycle') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Minutes/lesson') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Half-hour') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Active') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($feePlans as $plan)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $plan->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">RM {{ number_format($plan->cycle_fee_cents / 100, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $plan->lessons_per_cycle }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $plan->minutes_per_lesson_default }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $plan->allow_half_hour ? __('Yes') : __('No') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <x-status-badge :status="$plan->active ? 'active' : 'inactive'" />
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.fee-plans.edit', $plan) }}">
                                                {{ __('Edit') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

