<x-app-layout>
    <div class="py-8">
        <div class="mx-auto w-full max-w-[1800px] px-4 sm:px-6 lg:px-10">
            <div class="space-y-8">
                <div class="rounded-lg border border-white/10 bg-zinc-900 p-6">
                    <x-query-editor :ticket-number="2" :query="$query" />
                </div>
                <x-ticket-playground :ticket-number="2" title="Ticket 2" />
            </div>
        </div>
    </div>
</x-app-layout>
