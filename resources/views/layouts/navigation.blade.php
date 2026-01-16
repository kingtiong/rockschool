<nav x-data="{ drawerOpen: false }" class="bg-white border-b border-gray-100">
    <!-- Top Bar -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-3">
                <!-- Hamburger (always visible) -->
                <button
                    type="button"
                    @click="drawerOpen = true"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    aria-label="{{ __('Open menu') }}"
                >
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="shrink-0 flex items-center">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                </a>
            </div>

            <!-- Settings Dropdown -->
            <div class="flex items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>

    <!-- Drawer -->
    <div x-show="drawerOpen" class="fixed inset-0 z-40" style="display: none;">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/50" @click="drawerOpen = false"></div>

        <!-- Panel -->
        <div
            class="absolute inset-y-0 left-0 w-72 max-w-[85vw] bg-gray-900 text-white shadow-xl overflow-y-auto"
            @keydown.escape.window="drawerOpen = false"
        >
            <div class="flex items-center justify-between px-4 h-16 border-b border-white/10">
                <div class="flex items-center gap-2">
                    <x-application-logo class="block h-8 w-auto fill-current text-white" />
                    <div class="text-sm font-semibold">{{ config('app.name', 'Rockschool') }}</div>
                </div>
                <button
                    type="button"
                    @click="drawerOpen = false"
                    class="inline-flex items-center justify-center p-2 rounded-md text-white/80 hover:text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/40"
                    aria-label="{{ __('Close menu') }}"
                >
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-3 py-4 space-y-1">
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                    {{ __('Dashboard') }}
                </a>
                <a href="{{ route('schedule.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('schedule.*') ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                    {{ __('Schedule') }}
                </a>

                @if(auth()->user()->role === 'student')
                    <a href="{{ route('student.cycles.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('student.cycles.*') ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                        {{ __('Fees') }}
                    </a>
                @endif

                @if(auth()->user()->role === 'teacher')
                    <a href="{{ route('teacher.earnings.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('teacher.earnings.*') ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                        {{ __('Earnings') }}
                    </a>
                @endif

                @if(auth()->user()->role === 'management')
                    <div class="pt-4 pb-2 px-3 text-xs font-semibold text-white/60 uppercase tracking-wider">
                        {{ __('Timetable') }}
                    </div>
                    @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $d)
                        <a href="{{ route('management.timetable.index', ['day' => $d]) }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('management.timetable.*') && request('day', 'monday') === $d ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                            {{ ucfirst($d) }}
                        </a>
                    @endforeach

                    <div class="pt-4 pb-2 px-3 text-xs font-semibold text-white/60 uppercase tracking-wider">
                        {{ __('Management') }}
                    </div>
                    <a href="{{ route('management.branches.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('management.branches.*') ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                        {{ __('Branches') }}
                    </a>
                    <a href="{{ route('management.users.index', ['role' => 'student']) }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('management.users.*') ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                        {{ __('Users') }}
                    </a>
                    <a href="{{ route('management.fee-plans.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('management.fee-plans.*') ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                        {{ __('Fee Plans') }}
                    </a>
                    <a href="{{ route('management.enrollments.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('management.enrollments.*') ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                        {{ __('Enrollments') }}
                    </a>
                    <a href="{{ route('management.payments.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('management.payments.*') ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                        {{ __('Payments') }}
                    </a>
                    <a href="{{ route('management.teacher-shares.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('management.teacher-shares.*') ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                        {{ __('Teacher Shares') }}
                    </a>
                    <a href="{{ route('management.payouts.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('management.payouts.*') ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                        {{ __('Payouts') }}
                    </a>
                    <a href="{{ route('management.reschedule-requests.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('management.reschedule-requests.*') ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                        {{ __('Replacement') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</nav>
