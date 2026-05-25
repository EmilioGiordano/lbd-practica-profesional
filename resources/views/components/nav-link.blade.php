@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-zinc-100 px-1 pt-1 text-sm font-medium leading-5 text-zinc-100 focus:border-zinc-100 focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-zinc-400 hover:border-zinc-700 hover:text-zinc-100 focus:border-zinc-700 focus:outline-none focus:text-zinc-100 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
