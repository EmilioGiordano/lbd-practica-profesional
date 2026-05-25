<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold leading-tight text-zinc-50">
            Ticket 2
        </h1>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto w-full max-w-[1800px] px-4 sm:px-6 lg:px-10">
            <x-ticket-playground :ticket-number="2" title="Ticket 2" />
        </div>
    </div>
</x-app-layout>
