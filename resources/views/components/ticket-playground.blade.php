@props([
    'ticketNumber',
    'title',
])

<div
    x-data="ticketPlayground({
        endpoint: '{{ route('tickets.execute', $ticketNumber) }}',
        title: @js($title),
    })"
    x-init="load()"
    class="space-y-6"
>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-sky-700">SQL Playground</p>
                <h2 class="mt-2 text-2xl font-semibold text-gray-900" x-text="title"></h2>
                <p class="mt-1 text-sm text-gray-500">
                    Edita <code class="rounded bg-gray-100 px-2 py-1 text-xs">database/queries/ticket-{{ $ticketNumber }}.sql</code>, recarga y revisa el resultado.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                <span class="rounded-full bg-gray-100 px-3 py-1">
                    Filas: <strong x-text="rowCount"></strong>
                </span>
                <span class="rounded-full bg-gray-100 px-3 py-1">
                    Tiempo: <strong x-text="executionTimeMs"></strong> ms
                </span>
                <button
                    type="button"
                    @click="load()"
                    class="inline-flex items-center rounded-lg bg-sky-600 px-4 py-2 font-medium text-white transition hover:bg-sky-700"
                >
                    Recargar consulta
                </button>
            </div>
        </div>
    </div>

    <template x-if="error">
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" x-text="error"></div>
    </template>

    <div x-show="loading" class="rounded-2xl border border-gray-200 bg-white p-10 text-center text-sm text-gray-500 shadow-sm">
        Ejecutando consulta...
    </div>

    <div x-show="!loading" class="rounded-2xl border border-gray-200 bg-white shadow-sm" x-cloak>
        <template x-if="columns.length === 0 && !error">
            <div class="p-6 text-sm text-gray-500">
                La consulta se ejecuto correctamente pero no devolvio filas ni columnas.
            </div>
        </template>

        <template x-if="columns.length > 0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <template x-for="column in columns" :key="column">
                                <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-gray-600" x-text="column"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <template x-for="(row, rowIndex) in paginatedRows" :key="rowIndex">
                            <tr class="hover:bg-sky-50/40">
                                <template x-for="column in columns" :key="`${rowIndex}-${column}`">
                                    <td class="max-w-xs px-4 py-3 align-top text-gray-700">
                                        <span class="break-words" x-text="formatCell(row[column])"></span>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>

        <div class="flex flex-col gap-4 border-t border-gray-200 px-4 py-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <label for="per-page-{{ $ticketNumber }}">Filas por pagina</label>
                <select
                    id="per-page-{{ $ticketNumber }}"
                    x-model.number="perPage"
                    @change="page = 1"
                    class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500"
                >
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                </select>
            </div>

            <div class="flex items-center gap-3 text-sm text-gray-600">
                <button
                    type="button"
                    @click="prevPage()"
                    :disabled="page === 1"
                    class="rounded-lg border border-gray-300 px-3 py-2 transition disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Anterior
                </button>
                <span>Pagina <strong x-text="page"></strong> de <strong x-text="totalPages"></strong></span>
                <button
                    type="button"
                    @click="nextPage()"
                    :disabled="page >= totalPages"
                    class="rounded-lg border border-gray-300 px-3 py-2 transition disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Siguiente
                </button>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            function ticketPlayground({ endpoint, title }) {
                return {
                    endpoint,
                    title,
                    columns: [],
                    rows: [],
                    loading: false,
                    error: null,
                    executionTimeMs: 0,
                    rowCount: 0,
                    page: 1,
                    perPage: 25,
                    get totalPages() {
                        return Math.max(1, Math.ceil(this.rows.length / this.perPage));
                    },
                    get paginatedRows() {
                        const start = (this.page - 1) * this.perPage;
                        return this.rows.slice(start, start + this.perPage);
                    },
                    async load() {
                        this.loading = true;
                        this.error = null;

                        try {
                            const response = await fetch(`${this.endpoint}${window.location.search}`, {
                                headers: {
                                    Accept: 'application/json',
                                },
                            });

                            const payload = await response.json().catch(() => ({}));

                            if (!response.ok) {
                                throw new Error(payload.message || 'Error al ejecutar la consulta.');
                            }

                            this.columns = payload.columns || [];
                            this.rows = payload.rows || [];
                            this.executionTimeMs = payload.executionTimeMs || 0;
                            this.rowCount = payload.rowCount || 0;
                            this.page = 1;
                        } catch (error) {
                            this.columns = [];
                            this.rows = [];
                            this.executionTimeMs = 0;
                            this.rowCount = 0;
                            this.error = error.message || 'Error inesperado.';
                        } finally {
                            this.loading = false;
                        }
                    },
                    nextPage() {
                        if (this.page < this.totalPages) {
                            this.page++;
                        }
                    },
                    prevPage() {
                        if (this.page > 1) {
                            this.page--;
                        }
                    },
                    formatCell(value) {
                        if (value === null || value === undefined) {
                            return 'NULL';
                        }

                        if (typeof value === 'object') {
                            return JSON.stringify(value);
                        }

                        return String(value);
                    },
                };
            }
        </script>
    @endpush
@endonce
