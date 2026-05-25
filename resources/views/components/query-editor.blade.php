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
            <!-- Edit Button -->
            <button
                x-show="!editing"
                @click="toggleEdit()"
                class="rounded-lg border border-white/10 bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-300 hover:bg-zinc-700 transition"
            >
                ✎ Editar
            </button>

            <!-- Save/Cancel Buttons -->
            <div x-show="editing" class="flex gap-2">
                <button
                    @click="saveQuery()"
                    :disabled="saving"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50 transition"
                >
                    <span x-show="!saving">✓ Guardar</span>
                    <span x-show="saving">Guardando...</span>
                </button>
                <button
                    @click="toggleEdit()"
                    class="rounded-lg border border-white/10 bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-300 hover:bg-zinc-700 transition"
                >
                    ✕ Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Display Mode -->
    <div x-show="!editing" class="rounded-lg border border-white/10 bg-zinc-950 p-4 overflow-hidden">
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
