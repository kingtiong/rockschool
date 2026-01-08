<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $role === 'teacher' ? __('Teachers') : __('Students') }}
            </h2>

            <div class="flex items-center gap-3">
                <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.users.index', ['role' => 'student']) }}">
                    {{ __('Students') }}
                </a>
                <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.users.index', ['role' => 'teacher']) }}">
                    {{ __('Teachers') }}
                </a>
                @if($role === 'teacher')
                    <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.users.teachers.create') }}">
                        {{ __('Create teacher') }}
                    </a>
                @else
                    <a class="underline text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('management.users.students.create') }}">
                        {{ __('Create student') }}
                    </a>
                @endif
            </div>
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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Email') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($users as $u)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $u->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $u->email }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No users yet.') }}</td>
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

