<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Teacher Shares') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Teacher') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Current share') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($teachers as $teacher)
                                    @php $share = $currentShares->get($teacher->id); @endphp
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $teacher->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $share?->percent ?? 0 }}%
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <form method="POST" action="{{ route('management.teacher-shares.upsert', $teacher) }}" class="inline-flex items-center gap-2">
                                                @csrf
                                                <input name="percent" type="number" min="0" max="100" class="w-24 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" value="{{ old('percent', $share?->percent ?? 0) }}" />
                                                <x-primary-button>{{ __('Save') }}</x-primary-button>
                                            </form>
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

