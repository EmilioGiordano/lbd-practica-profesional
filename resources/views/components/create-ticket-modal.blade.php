<div x-data="ticketCreator()" x-cloak>
    <!-- Button -->
    <button
        @click="open = true; fetchNextNumber()"
        class="inline-flex items-center rounded-lg border border-white/10 bg-zinc-900 px-3 py-2 text-sm font-medium leading-4 text-zinc-300 transition hover:bg-zinc-800 hover:text-zinc-100 focus:outline-none"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span class="ml-2">Nuevo Ticket</span>
    </button>

    <!-- Modal Backdrop -->
    <div
        x-show="open"
        x-transition.opacity.duration.200
        class="fixed top-0 left-0 right-0 bottom-0 z-40 bg-black/50 backdrop-blur-sm"
        @click="open = false"
    ></div>

    <!-- Modal Container - CENTERED -->
    <div
        x-show="open"
        x-transition.opacity.duration.200
        class="fixed top-0 left-0 right-0 bottom-0 z-50 w-screen h-screen flex items-center justify-center"
    >
        <!-- Modal Box -->
        <div
            @click.stop
            class="w-full max-w-md bg-zinc-900 border border-white/10 rounded-lg shadow-2xl overflow-hidden"
        >
            <!-- Header -->
            <div class="border-b border-white/10 px-6 py-4 bg-zinc-950">
                <h2 class="text-lg font-semibold text-zinc-100">Crear nuevo ticket</h2>
            </div>

            <!-- Form Content -->
            <form @submit.prevent="submit" class="space-y-4 p-6">
                <!-- Número -->
                <div>
                    <label class="block text-sm font-medium text-zinc-300 mb-2">Número del ticket</label>
                    <input
                        x-model.number="form.numero"
                        type="number"
                        min="1"
                        disabled
                        class="w-full rounded-lg border border-white/10 bg-zinc-800 px-3 py-2 text-zinc-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    />
                    <p class="text-xs text-zinc-400 mt-1">Se asigna automáticamente</p>
                </div>

                <!-- Título -->
                <div>
                    <label class="block text-sm font-medium text-zinc-300 mb-2">Título del ticket</label>
                    <input
                        x-model="form.titulo"
                        type="text"
                        placeholder="Ej: Consulta de vendedores"
                        class="w-full rounded-lg border border-white/10 bg-zinc-800 px-3 py-2 text-zinc-100 placeholder-zinc-500 focus:border-white/30 focus:outline-none transition"
                        required
                        @keydown.escape="open = false"
                    />
                </div>

                <!-- Error Message -->
                <div x-show="error" class="rounded-lg border border-red-500/20 bg-red-900/20 p-3">
                    <p class="text-sm text-red-400" x-text="error"></p>
                </div>

                <!-- Success Message -->
                <div x-show="success" class="rounded-lg border border-green-500/20 bg-green-900/20 p-3">
                    <p class="text-sm text-green-400" x-text="success"></p>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                    <button
                        type="button"
                        @click="open = false"
                        class="rounded-lg border border-white/10 bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-300 hover:bg-zinc-700 transition"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        <span x-show="!loading">Crear</span>
                        <span x-show="loading">Creando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function ticketCreator() {
            return {
                open: false,
                loading: false,
                error: '',
                success: '',
                form: {
                    numero: 0,
                    titulo: '',
                },

                async fetchNextNumber() {
                    try {
                        const response = await fetch('{{ route("tickets.next-number") }}');
                        const data = await response.json();
                        if (data.nextNumber) {
                            this.form.numero = data.nextNumber;
                        }
                    } catch (e) {
                        console.error('Error fetching next number:', e);
                    }
                },

                async submit() {
                    this.loading = true;
                    this.error = '';
                    this.success = '';

                    try {
                        const response = await fetch('{{ route("tickets.api.create") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(this.form),
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.success = data.message;
                            this.form.titulo = '';
                            setTimeout(() => {
                                window.location.href = `/tickets/${data.number}`;
                            }, 500);
                        } else {
                            this.error = data.message || 'Error al crear el ticket';
                        }
                    } catch (error) {
                        this.error = 'Error de conexión: ' + error.message;
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</div>
