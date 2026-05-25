@props(['ticketNumber', 'query'])

@once
    @push('scripts')
        <script>
            window.queryEditors = window.queryEditors || {};

            function createQueryEditor(ticketNumber, initialQuery) {
                return {
                    ticketNumber,
                    originalQuery: initialQuery,
                    editingQuery: initialQuery,
                    editing: false,
                    saving: false,
                    expanded: true,
                    error: '',
                    success: '',

                    toggleEdit() {
                        this.editing = !this.editing;
                        if (!this.editing) {
                            this.editingQuery = this.originalQuery;
                        }
                        this.error = '';
                        this.success = '';
                    },

                    toggleExpanded() {
                        this.expanded = !this.expanded;
                    },

                    async saveQuery() {
                        if (this.editingQuery.trim() === '') {
                            this.error = 'La query no puede estar vacía';
                            return;
                        }

                        this.saving = true;
                        this.error = '';
                        this.success = '';

                        try {
                            const response = await fetch('{{ route("tickets.update-query") }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    numero: this.ticketNumber,
                                    query: this.editingQuery.trim(),
                                }),
                            });

                            const data = await response.json();

                            if (response.ok) {
                                this.originalQuery = this.editingQuery;
                                this.success = 'Query guardada exitosamente';
                                setTimeout(() => {
                                    this.editing = false;
                                    this.success = '';
                                }, 1500);
                            } else {
                                this.error = data.message || 'Error al guardar';
                            }
                        } catch (error) {
                            this.error = 'Error de conexión: ' + error.message;
                        } finally {
                            this.saving = false;
                        }
                    },
                };
            }
        </script>
    @endpush
@endonce

<div x-data="createQueryEditor({{ $ticketNumber }}, `{{ str_replace('`', '\\`', $query) }}`)" class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-zinc-100">SQL Query</h3>
        <div class="flex gap-2">
            <!-- Expand/Collapse Button -->
            <button
                @click="toggleExpanded()"
                class="inline-flex items-center justify-center rounded-lg border border-white/10 bg-zinc-800 px-3 py-2 text-zinc-300 hover:bg-zinc-700 transition"
                :title="expanded ? 'Contraer' : 'Expandir'"
            >
                <svg x-show="expanded" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <svg x-show="!expanded" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Edit Button -->
            <button
                x-show="!editing"
                @click="toggleEdit()"
                class="inline-flex items-center justify-center rounded-lg border border-white/10 bg-zinc-800 px-3 py-2 text-zinc-300 hover:bg-zinc-700 transition"
                title="Editar query"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>

            <!-- Save/Cancel Buttons -->
            <div x-show="editing" class="flex gap-2">
                <button
                    @click="saveQuery()"
                    :disabled="saving"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50 transition"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span x-show="!saving">Guardar</span>
                    <span x-show="saving">Guardando...</span>
                </button>
                <button
                    @click="toggleEdit()"
                    class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-300 hover:bg-zinc-700 transition"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Display Mode -->
    <div x-show="expanded && !editing" class="rounded-lg border border-white/10 bg-zinc-950 p-4 overflow-hidden">
        <pre class="text-zinc-300 text-sm font-mono overflow-x-auto whitespace-pre-wrap break-words"><code x-text="originalQuery"></code></pre>
    </div>

    <!-- Edit Mode -->
    <div x-show="editing" class="space-y-3">
        <textarea
            x-model="editingQuery"
            class="w-full h-64 rounded-lg border border-white/10 bg-zinc-950 px-4 py-3 font-mono text-sm text-zinc-100 placeholder-zinc-500 focus:border-white/30 focus:outline-none resize-none"
            placeholder="SELECT * FROM carriers"
            spellcheck="false"
        ></textarea>

        <!-- Error Message -->
        <div x-show="error" class="rounded-lg border border-red-500/20 bg-red-900/20 p-3">
            <p class="text-sm text-red-400" x-text="error"></p>
        </div>

        <!-- Success Message -->
        <div x-show="success" class="rounded-lg border border-green-500/20 bg-green-900/20 p-3">
            <p class="text-sm text-green-400" x-text="success"></p>
        </div>
    </div>
</div>
