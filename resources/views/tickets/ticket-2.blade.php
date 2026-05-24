<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Ticket 2
        </h1>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-ticket-playground :ticket-number="2" title="Ticket 2" />
        </div>
    </div>
</x-app-layout>
