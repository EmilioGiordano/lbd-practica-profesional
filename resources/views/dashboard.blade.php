<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold tracking-tight text-zinc-100">
            Consigna del práctico
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto w-full max-w-3xl px-4 sm:px-6">
            <article class="rounded-2xl border border-white/10 bg-[#15110d] shadow-2xl shadow-black/40">
                <div class="px-7 py-10 sm:px-14 sm:py-14">
                    <div class="divide-y divide-white/10">

                        {{-- ENCABEZADO DEL DOCUMENTO --}}
                        <header class="space-y-6 pb-10">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-amber-400/80">
                                Laboratorio de Bases de Datos · Práctico integrador
                            </p>

                            <div class="space-y-4">
                                <h1 class="text-3xl font-bold tracking-tight text-zinc-50">
                                    Sprint de Tickets
                                </h1>
                                <p class="text-[17px] leading-relaxed text-stone-300">
                                    Una serie de consignas para resolver con SQL contra una base de datos poblada,
                                    visualizando los resultados dentro de esta misma aplicación. La idea es salir de
                                    las consultas sueltas en pgAdmin y trabajar en un entorno más cercano al
                                    desarrollo real.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @foreach ([
                                    'DDL provisto',
                                    'Seed con gran volumen de filas',
                                    'Una consulta por ticket',
                                    'Resultados en datatable',
                                ] as $chip)
                                    <span class="inline-flex items-center rounded-full border border-white/10 bg-white/[0.03] px-3 py-1 text-xs font-medium text-stone-300">
                                        {{ $chip }}
                                    </span>
                                @endforeach
                            </div>

                            <div>
                                <a href="{{ route('tickets.show', 1) }}"
                                   class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-zinc-950 transition hover:bg-amber-400">
                                    Ir al primer ticket
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            </div>
                        </header>

                        {{-- 01 · SPRINT DE TICKETS --}}
                        <section class="space-y-5 py-10">
                            <div class="flex items-baseline gap-3">
                                <span class="font-mono text-sm font-semibold text-amber-400/70">01</span>
                                <h2 class="text-xl font-semibold tracking-tight text-zinc-100">El material</h2>
                            </div>

                            <div class="space-y-4 text-[15px] leading-7 text-stone-300">
                                <p>
                                    Recibís un <span class="font-medium text-zinc-100">DDL.sql</span> con la definición
                                    de todas las tablas, dominios y relaciones, junto a un
                                    <span class="font-medium text-zinc-100">seed.sql</span> que puebla la base con
                                    muchas filas. Ese volumen es a propósito: hace que tenga sentido implementar índices
                                    y medir el rendimiento <span class="italic">con y sin</span> ellos.
                                </p>

                                <ul class="list-disc space-y-2 pl-5 marker:text-amber-400/60">
                                    <li>
                                        Por cada enunciado armás una consulta SQL: elegís qué campos mostrar, los
                                        <span class="font-mono text-[13px] text-zinc-200">JOIN</span>, los filtros y el
                                        ordenamiento.
                                    </li>
                                    <li>
                                        Podés trabajar las consultas en pgAdmin o en la herramienta que prefieras. La
                                        aplicación es un extra para acercarte a un entorno fullstack real.
                                    </li>
                                    <li>
                                        La app renderiza el resultado de cualquier consulta sin importar los campos. No
                                        es un estándar, pero acá conviene: siempre vas a ver lo que devuelve tu query.
                                    </li>
                                </ul>
                            </div>
                        </section>

                        {{-- 02 · VISUALIZACIÓN --}}
                        <section class="space-y-5 py-10">
                            <div class="flex items-baseline gap-3">
                                <span class="font-mono text-sm font-semibold text-amber-400/70">02</span>
                                <h2 class="text-xl font-semibold tracking-tight text-zinc-100">Visualización de consultas</h2>
                            </div>

                            <div class="space-y-5 text-[15px] leading-7 text-stone-300">
                                <p>
                                    Corrés este proyecto PHP localmente, lo conectás a tu base con tus credenciales y
                                    cada consulta se renderiza en una vista web como un
                                    <span class="font-medium text-zinc-100">datatable</span>. Cada tarea del sprint es
                                    un ticket, y cada ticket se compone de tres piezas:
                                </p>

                                <dl class="divide-y divide-white/5 overflow-hidden rounded-xl border border-white/10 bg-black/20">
                                    @foreach ([
                                        ['Vista web', 'El enunciado y la tabla de resultados.'],
                                        ['consulta.sql', 'El archivo con tu query, editable desde la propia vista.'],
                                        ['Entrada en la navbar', 'Un acceso directo al ticket en la barra superior.'],
                                    ] as [$term, $desc])
                                        <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:gap-4">
                                            <dt class="font-medium text-zinc-100 sm:w-48 sm:shrink-0">{{ $term }}</dt>
                                            <dd class="text-stone-400">{{ $desc }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        </section>

                        {{-- 03 · PUESTA EN MARCHA --}}
                        <section class="space-y-5 py-10">
                            <div class="flex items-baseline gap-3">
                                <span class="font-mono text-sm font-semibold text-amber-400/70">03</span>
                                <h2 class="text-xl font-semibold tracking-tight text-zinc-100">Puesta en marcha</h2>
                            </div>

                            <div class="space-y-5 text-[15px] leading-7 text-stone-300">
                                <p>
                                    Levantar el entorno es sencillo. El
                                    <span class="font-mono text-[13px] text-zinc-200">README.md</span> del repositorio
                                    detalla cada paso y las dependencias necesarias.
                                </p>

                                <ol class="space-y-3">
                                    @foreach ([
                                        'Cloná el repositorio o descargá el zip.',
                                        'Instalá las dependencias (PHP y Node) según el README.',
                                        'Copiá el .env y cargá las credenciales de tu base de datos.',
                                        'Creá la base y ejecutá el DDL y el seed para poblarla.',
                                        'Levantá el servidor y entrá a los tickets.',
                                    ] as $i => $paso)
                                        <li class="flex items-start gap-3">
                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/[0.03] text-xs font-semibold text-amber-300">
                                                {{ $i + 1 }}
                                            </span>
                                            <span class="pt-0.5">{{ $paso }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        </section>

                        {{-- 04 · EVALUACIÓN --}}
                        <section class="space-y-5 py-10">
                            <div class="flex flex-wrap items-baseline gap-x-3 gap-y-2">
                                <span class="font-mono text-sm font-semibold text-amber-400/70">04</span>
                                <h2 class="text-xl font-semibold tracking-tight text-zinc-100">Evaluación</h2>
                                <span class="inline-flex items-center rounded-full border border-emerald-400/20 bg-emerald-400/10 px-2.5 py-0.5 text-xs font-medium text-emerald-300">
                                    Previsto
                                </span>
                            </div>

                            <div class="space-y-4 text-[15px] leading-7 text-stone-300">
                                <p>
                                    La idea es exportar un documento con cada ticket, su consulta asociada y el
                                    <span class="font-medium text-zinc-100">tiempo promedio de ejecución</span>: la app
                                    corre la misma query varias veces y promedia el tiempo. Así la corrección se vuelve
                                    mucho más simple.
                                </p>
                                <p>
                                    Desde el frontend completás tanto la consulta como una descripción de lo realizado
                                    —las explicaciones que creas pertinentes—, y ambas cosas se incluyen en la
                                    exportación.
                                </p>
                            </div>
                        </section>

                        {{-- 05 · PARTE PEDAGÓGICA --}}
                        <section class="space-y-5 pt-10">
                            <div class="flex items-baseline gap-3">
                                <span class="font-mono text-sm font-semibold text-amber-400/70">05</span>
                                <h2 class="text-xl font-semibold tracking-tight text-zinc-100">El objetivo</h2>
                            </div>

                            <div class="space-y-5 text-[15px] leading-7 text-stone-300">
                                <p>
                                    El foco está en poner a prueba tu criterio al resolver consultas complejas que unen
                                    varias tablas. Entender el
                                    <span class="font-medium text-zinc-100">contexto y el universo del discurso</span>
                                    es fundamental: cada consigna plantea un escenario distinto que te obliga a pensar
                                    más allá de la consulta en sí.
                                </p>

                                <dl class="space-y-4">
                                    @foreach ([
                                        ['Audiencia', '¿Quién usará mayormente la vista? Un departamento específico, empleados operativos, técnicos, de marketing o líderes. Eso cambia qué mostrar y cómo.'],
                                        ['Volumen', '¿Cuántas consultas recibirá por día? Considerá las filas devueltas y el tiempo de ejecución, y evaluá si vale la pena un índice, cómo implementarlo y cómo se compara con y sin él.'],
                                        ['Rendimiento', 'Una consulta pesada que trae muchas filas sin LIMIT (por ejemplo, una exportación). Acá el rendimiento es crucial: analizá incluso si conviene un ORDER BY que encarece la búsqueda.'],
                                    ] as [$term, $desc])
                                        <div class="border-l-2 border-amber-400/30 pl-4">
                                            <dt class="text-sm font-semibold text-amber-200">{{ $term }}</dt>
                                            <dd class="mt-1 text-stone-400">{{ $desc }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        </section>

                    </div>
                </div>
            </article>
        </div>
    </div>
</x-app-layout>
