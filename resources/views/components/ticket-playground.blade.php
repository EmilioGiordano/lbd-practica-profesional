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
    class="space-y-6 text-stone-100"
>
    <section class="rounded-[32px] border border-[#3a3029] bg-[linear-gradient(180deg,#221c18_0%,#171310_100%)] p-6 shadow-[0_30px_80px_-35px_rgba(0,0,0,0.75)]">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-4xl">
                <div class="inline-flex items-center rounded-full border border-white/5 bg-black/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-stone-500">
                    SQL Playground · Backoffice
                </div>
                <h2 class="mt-4 text-4xl font-semibold tracking-tight text-stone-50" x-text="title"></h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-stone-400">
                    Entorno de validacion para consultas SQL del examen. La ejecucion aplica reglas de seguridad, soporte de parametros y renderizado dinamico de columnas.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button
                    type="button"
                    @click="load()"
                    class="inline-flex items-center rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600"
                >
                    Recargar consulta
                </button>
                <div class="rounded-2xl border border-white/5 bg-black/10 px-4 py-3 text-sm text-stone-300">
                    Archivo: <span class="font-medium text-stone-100">ticket-{{ $ticketNumber }}.sql</span>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-[24px] border border-[#3a3029] bg-[#211b18] px-5 py-4">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="grid gap-3 sm:grid-cols-3 xl:flex xl:flex-wrap xl:items-center">
                    <div class="sql-inline-stat">
                        <span class="sql-inline-stat__label">Filas</span>
                        <span class="sql-inline-stat__value text-emerald-300" x-text="rowCount"></span>
                    </div>
                    <div class="sql-inline-stat">
                        <span class="sql-inline-stat__label">Columnas</span>
                        <span class="sql-inline-stat__value text-stone-100" x-text="columns.length"></span>
                    </div>
                    <div class="sql-inline-stat">
                        <span class="sql-inline-stat__label">Tiempo</span>
                        <span class="sql-inline-stat__value text-amber-300">
                            <span x-text="executionTimeMs"></span> ms
                        </span>
                    </div>
                </div>

                <div class="min-w-0 rounded-2xl border border-white/5 bg-black/10 px-4 py-3 xl:max-w-[420px]">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-500">Endpoint</p>
                    <p class="mt-1 truncate text-sm font-medium text-stone-200 xl:text-right">/api/tickets/{{ $ticketNumber }}/execute</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 2xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="overflow-hidden rounded-[30px] border border-[#3a3029] bg-[#1a1512] shadow-[0_18px_45px_-28px_rgba(0,0,0,0.8)]">
            <div class="border-b border-white/5 px-6 py-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-2xl font-semibold text-stone-100">Resultado de la consulta</p>
                        <p class="mt-1 text-sm text-stone-400">
                            Tabla dinamica generada desde el JSON devuelto por el backend.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="rounded-full border border-white/5 bg-black/10 px-3 py-2 text-xs font-medium text-stone-400">
                            Maximo visible: <span class="text-stone-200" x-text="rows.length"></span> filas
                        </div>
                        <div class="rounded-full border border-white/5 bg-black/10 px-3 py-2 text-xs font-medium text-stone-400">
                            Ticket {{ $ticketNumber }}
                        </div>
                    </div>
                </div>
            </div>

            <template x-if="error">
                <div class="m-6 rounded-2xl border border-red-500/20 bg-red-500/10 px-5 py-4 text-sm text-red-200" x-text="error"></div>
            </template>

            <div x-show="loading" class="px-6 py-16">
                <div class="mx-auto flex max-w-sm flex-col items-center gap-4 text-center">
                    <div class="sql-spinner"></div>
                    <div>
                        <p class="text-base font-semibold text-stone-100">Ejecutando consulta</p>
                        <p class="mt-1 text-sm text-stone-400">Cargando filas y columnas del resultado...</p>
                    </div>
                </div>
            </div>

            <div x-show="!loading" x-cloak>
                <template x-if="columns.length === 0 && !error">
                    <div class="px-6 py-12 text-center">
                        <div class="mx-auto max-w-md rounded-[24px] border border-dashed border-[#3a3029] bg-[#211b18] px-6 py-10">
                            <p class="text-base font-semibold text-stone-100">Consulta ejecutada</p>
                            <p class="mt-2 text-sm leading-6 text-stone-400">
                                La consulta termino correctamente pero no devolvio filas ni columnas para mostrar.
                            </p>
                        </div>
                    </div>
                </template>

                <template x-if="columns.length > 0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="border-b border-white/5 bg-[#211b18] text-stone-300">
                                <tr>
                                    <template x-for="column in columns" :key="column">
                                        <th class="whitespace-nowrap px-5 py-4 text-left text-[11px] font-semibold uppercase tracking-[0.24em]" x-text="column"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 bg-[#1a1512]">
                                <template x-for="(row, rowIndex) in paginatedRows" :key="rowIndex">
                                    <tr class="hover:bg-[#221c18]">
                                        <template x-for="column in columns" :key="`${rowIndex}-${column}`">
                                            <td class="max-w-sm px-5 py-4 align-top text-stone-300">
                                                <span class="sql-cell" :class="{ 'sql-cell-null': row[column] === null || row[column] === undefined }" x-text="formatCell(row[column])"></span>
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>
        </div>

        <aside class="space-y-6">
            <section class="rounded-[28px] border border-[#3a3029] bg-[#1a1512] p-5">
                <p class="text-lg font-semibold text-stone-100">Controles</p>
                <p class="mt-1 text-sm text-stone-400">Herramientas de navegacion y lectura.</p>

                <div class="mt-5 space-y-4">
                    <div class="rounded-2xl border border-white/5 bg-[#211b18] p-4">
                        <label for="per-page-{{ $ticketNumber }}" class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-500">Filas por pagina</label>
                        <select
                            id="per-page-{{ $ticketNumber }}"
                            x-model.number="perPage"
                            @change="page = 1"
                            class="mt-3 w-full rounded-2xl border border-[#3a3029] bg-[#171310] px-4 py-3 text-sm text-stone-200 focus:border-emerald-700 focus:ring-emerald-900"
                        >
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                    </div>

                    <div class="rounded-2xl border border-white/5 bg-[#211b18] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-500">Paginacion</p>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-full border border-white/5 bg-black/10 px-3 py-2 text-center text-xs font-medium text-stone-300">
                                Pagina <span x-text="page"></span> de <span x-text="totalPages"></span>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    @click="prevPage()"
                                    :disabled="page === 1"
                                    class="inline-flex items-center justify-center rounded-2xl border border-[#3a3029] bg-[#171310] px-4 py-2.5 text-sm font-medium text-stone-200 transition hover:bg-[#221c18] disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Anterior
                                </button>
                                <button
                                    type="button"
                                    @click="nextPage()"
                                    :disabled="page >= totalPages"
                                    class="inline-flex items-center justify-center rounded-2xl border border-[#3a3029] bg-[#171310] px-4 py-2.5 text-sm font-medium text-stone-200 transition hover:bg-[#221c18] disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Siguiente
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-[28px] border border-[#3a3029] bg-[#1a1512] p-5">
                <p class="text-lg font-semibold text-stone-100">Resumen tecnico</p>
                <p class="mt-1 text-sm text-stone-400">Estado rapido del ticket actual.</p>

                <div class="mt-5 space-y-3">
                    <div class="sql-status-row">
                        <span>Vista</span>
                        <strong class="text-stone-100">{{ $title }}</strong>
                    </div>
                    <div class="sql-status-row">
                        <span>Columnas</span>
                        <strong class="text-stone-100" x-text="columns.length"></strong>
                    </div>
                    <div class="sql-status-row">
                        <span>Filas visibles</span>
                        <strong class="text-emerald-300" x-text="rowCount"></strong>
                    </div>
                    <div class="sql-status-row">
                        <span>Tiempo</span>
                        <strong class="text-amber-300"><span x-text="executionTimeMs"></span> ms</strong>
                    </div>
                </div>
            </section>
        </aside>
    </section>
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
