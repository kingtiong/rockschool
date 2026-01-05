<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('schedule.index')" :active="request()->routeIs('schedule.*')">
                        {{ __('Schedule') }}
                    </x-nav-link>
                    @if(auth()->user()->role === 'student')
                        <x-nav-link :href="route('student.cycles.index')" :active="request()->routeIs('student.cycles.*')">
                            {{ __('Fees') }}
                        </x-nav-link>
                    @endif
                    @if(auth()->user()->role === 'teacher')
                        <x-nav-link :href="route('teacher.earnings.index')" :active="request()->routeIs('teacher.earnings.*')">
                            {{ __('Earnings') }}
                        </x-nav-link>
                    @endif
                    @if(auth()->user()->role === 'management')
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ request()->routeIs('management.timetable.*') ? 'border-indigo-400 text-gray-900 focus:border-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:text-gray-700 focus:border-gray-300' }}">
                                    <div>{{ __('Timetable') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('management.timetable.index', ['day' => 'monday'])">
                                    {{ __('Monday') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('management.timetable.index', ['day' => 'tuesday'])">
                                    {{ __('Tuesday') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('management.timetable.index', ['day' => 'wednesday'])">
                                    {{ __('Wednesday') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('management.timetable.index', ['day' => 'thursday'])">
                                    {{ __('Thursday') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('management.timetable.index', ['day' => 'friday'])">
                                    {{ __('Friday') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('management.timetable.index', ['day' => 'saturday'])">
                                    {{ __('Saturday') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('management.timetable.index', ['day' => 'sunday'])">
                                    {{ __('Sunday') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                        <x-nav-link :href="route('management.branches.index')" :active="request()->routeIs('management.branches.*')">
                            {{ __('Branches') }}
                        </x-nav-link>
                        <x-nav-link :href="route('management.users.index', ['role' => 'student'])" :active="request()->routeIs('management.users.*')">
                            {{ __('Users') }}
                        </x-nav-link>
                        <x-nav-link :href="route('management.fee-plans.index')" :active="request()->routeIs('management.fee-plans.*')">
                            {{ __('Fee Plans') }}
                        </x-nav-link>
                        <x-nav-link :href="route('management.enrollments.index')" :active="request()->routeIs('management.enrollments.*')">
                            {{ __('Enrollments') }}
                        </x-nav-link>
                        <x-nav-link :href="route('management.payments.index')" :active="request()->routeIs('management.payments.*')">
                            {{ __('Payments') }}
                        </x-nav-link>
                        <x-nav-link :href="route('management.teacher-shares.index')" :active="request()->routeIs('management.teacher-shares.*')">
                            {{ __('Teacher Shares') }}
                        </x-nav-link>
                        <x-nav-link :href="route('management.payouts.index')" :active="request()->routeIs('management.payouts.*')">
                            {{ __('Payouts') }}
                        </x-nav-link>
                        <x-nav-link :href="route('management.reschedule-requests.index')" :active="request()->routeIs('management.reschedule-requests.*')">
                            {{ __('Reschedule') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
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

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('schedule.index')" :active="request()->routeIs('schedule.*')">
                {{ __('Schedule') }}
            </x-responsive-nav-link>
            @if(auth()->user()->role === 'student')
                <x-responsive-nav-link :href="route('student.cycles.index')" :active="request()->routeIs('student.cycles.*')">
                    {{ __('Fees') }}
                </x-responsive-nav-link>
            @endif
            @if(auth()->user()->role === 'teacher')
                <x-responsive-nav-link :href="route('teacher.earnings.index')" :active="request()->routeIs('teacher.earnings.*')">
                    {{ __('Earnings') }}
                </x-responsive-nav-link>
            @endif
            @if(auth()->user()->role === 'management')
                <div class="px-4 pt-4 pb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ __('Timetable') }}
                </div>
                <x-responsive-nav-link :href="route('management.timetable.index', ['day' => 'monday'])" :active="request()->routeIs('management.timetable.*') && request('day', 'monday') === 'monday'">
                    {{ __('Monday') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.timetable.index', ['day' => 'tuesday'])" :active="request()->routeIs('management.timetable.*') && request('day') === 'tuesday'">
                    {{ __('Tuesday') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.timetable.index', ['day' => 'wednesday'])" :active="request()->routeIs('management.timetable.*') && request('day') === 'wednesday'">
                    {{ __('Wednesday') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.timetable.index', ['day' => 'thursday'])" :active="request()->routeIs('management.timetable.*') && request('day') === 'thursday'">
                    {{ __('Thursday') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.timetable.index', ['day' => 'friday'])" :active="request()->routeIs('management.timetable.*') && request('day') === 'friday'">
                    {{ __('Friday') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.timetable.index', ['day' => 'saturday'])" :active="request()->routeIs('management.timetable.*') && request('day') === 'saturday'">
                    {{ __('Saturday') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.timetable.index', ['day' => 'sunday'])" :active="request()->routeIs('management.timetable.*') && request('day') === 'sunday'">
                    {{ __('Sunday') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.branches.index')" :active="request()->routeIs('management.branches.*')">
                    {{ __('Branches') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.users.index', ['role' => 'student'])" :active="request()->routeIs('management.users.*')">
                    {{ __('Users') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.fee-plans.index')" :active="request()->routeIs('management.fee-plans.*')">
                    {{ __('Fee Plans') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.enrollments.index')" :active="request()->routeIs('management.enrollments.*')">
                    {{ __('Enrollments') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.payments.index')" :active="request()->routeIs('management.payments.*')">
                    {{ __('Payments') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.teacher-shares.index')" :active="request()->routeIs('management.teacher-shares.*')">
                    {{ __('Teacher Shares') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.payouts.index')" :active="request()->routeIs('management.payouts.*')">
                    {{ __('Payouts') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.reschedule-requests.index')" :active="request()->routeIs('management.reschedule-requests.*')">
                    {{ __('Reschedule') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
