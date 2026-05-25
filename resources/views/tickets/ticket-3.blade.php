<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold leading-tight text-zinc-50">
            Ticket 3
        </h1>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto w-full max-w-[1800px] px-4 sm:px-6 lg:px-10">
            <x-ticket-playground :ticket-number="3" title="Ticket 3" />
        </div>
    </div>
</x-app-layout>
