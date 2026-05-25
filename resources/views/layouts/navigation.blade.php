<nav x-data="{ open: false }" class="border-b border-white/10 bg-[#09090b]/95 backdrop-blur">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('tickets.show', 1) }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-zinc-100" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('tickets.show', 1)" :active="request()->routeIs('tickets.show') && request()->route('ticketNumber') == 1">
                        {{ __('Ticket 1') }}
                    </x-nav-link>
                    <x-nav-link :href="route('tickets.show', 2)" :active="request()->routeIs('tickets.show') && request()->route('ticketNumber') == 2">
                        {{ __('Ticket 2') }}
                    </x-nav-link>
                    <x-nav-link :href="route('tickets.show', 3)" :active="request()->routeIs('tickets.show') && request()->route('ticketNumber') == 3">
                        {{ __('Ticket 3') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center rounded-lg border border-white/10 bg-zinc-900 px-3 py-2 text-sm font-medium leading-4 text-zinc-300 transition hover:bg-zinc-800 hover:text-zinc-100 focus:outline-none">
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
                @else
                    <span class="rounded-md border border-white/10 bg-zinc-900 px-3 py-1 text-sm font-medium text-zinc-300">
                        SQL Playground
                    </span>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-lg p-2 text-zinc-400 transition hover:bg-zinc-900 hover:text-zinc-100 focus:outline-none focus:bg-zinc-900 focus:text-zinc-100">
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
            <x-responsive-nav-link :href="route('tickets.show', 1)" :active="request()->routeIs('tickets.show') && request()->route('ticketNumber') == 1">
                {{ __('Ticket 1') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('tickets.show', 2)" :active="request()->routeIs('tickets.show') && request()->route('ticketNumber') == 2">
                {{ __('Ticket 2') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('tickets.show', 3)" :active="request()->routeIs('tickets.show') && request()->route('ticketNumber') == 3">
                {{ __('Ticket 3') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        @auth
            <div class="border-t border-white/10 pt-4 pb-1">
                <div class="px-4">
                    <div class="text-base font-medium text-zinc-100">{{ Auth::user()->name }}</div>
                    <div class="text-sm font-medium text-zinc-400">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

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
        @endauth
    </div>
</nav>
