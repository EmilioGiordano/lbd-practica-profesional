@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-2 border-zinc-100 bg-zinc-900 py-2 ps-3 pe-4 text-start text-base font-medium text-zinc-100 focus:border-zinc-100 focus:bg-zinc-900 focus:text-zinc-100 focus:outline-none transition duration-150 ease-in-out'
            : 'block w-full border-l-2 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-zinc-400 hover:border-zinc-700 hover:bg-zinc-900 hover:text-zinc-100 focus:border-zinc-700 focus:bg-zinc-900 focus:text-zinc-100 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
