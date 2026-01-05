<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Branches') }}
            </h2>
            <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.branches.create') }}">
                {{ __('Create branch') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($branches as $branch)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $branch->name }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($branch->active)
                                                <x-status-badge status="active" />
                                            @else
                                                <x-status-badge status="inactive" />
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.branches.edit', $branch) }}">
                                                    {{ __('Edit') }}
                                                </a>
                                                <form method="POST" action="{{ route('management.branches.destroy', $branch) }}" onsubmit="return confirm('{{ __('Delete this branch?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="underline text-sm text-red-600 hover:text-red-800">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No branches yet.') }}</td>
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

