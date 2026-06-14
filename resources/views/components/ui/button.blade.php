@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $variants = [
        'primary' => 'bg-emerald-700 text-white shadow-sm hover:bg-emerald-800 focus:ring-emerald-600',
        'success' => 'bg-emerald-700 text-white shadow-sm hover:bg-emerald-800 focus:ring-emerald-600',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 shadow-sm hover:bg-slate-50 focus:ring-emerald-600',
        'danger' => 'bg-red-600 text-white shadow-sm hover:bg-red-700 focus:ring-red-600',
        'warning' => 'bg-amber-500 text-white shadow-sm hover:bg-amber-600 focus:ring-amber-500',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
    ];

    $class = 'inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>
        @isset($icon)
            {{ $icon }}
        @endisset
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $class]) }}>
        @isset($icon)
            {{ $icon }}
        @endisset
        {{ $slot }}
    </button>
@endif
