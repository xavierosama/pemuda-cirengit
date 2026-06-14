@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'loadingText' => 'Memproses...',
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
        <svg x-cloak x-show="typeof submitting !== 'undefined' && submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-90" fill="currentColor" d="M21 12a9 9 0 0 0-9-9v4a5 5 0 0 1 5 5h4Z"></path>
        </svg>
        @isset($icon)
            {{ $icon }}
        @endisset
        <span x-show="!(typeof submitting !== 'undefined' && submitting)">{{ $slot }}</span>
        <span x-cloak x-show="typeof submitting !== 'undefined' && submitting">{{ $loadingText }}</span>
    </a>
@else
    <button type="{{ $type }}" x-bind:disabled="typeof submitting !== 'undefined' && submitting" {{ $attributes->merge(['class' => $class.' disabled:cursor-not-allowed disabled:opacity-80']) }}>
        <svg x-cloak x-show="typeof submitting !== 'undefined' && submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-90" fill="currentColor" d="M21 12a9 9 0 0 0-9-9v4a5 5 0 0 1 5 5h4Z"></path>
        </svg>
        @isset($icon)
            {{ $icon }}
        @endisset
        <span x-show="!(typeof submitting !== 'undefined' && submitting)">{{ $slot }}</span>
        <span x-cloak x-show="typeof submitting !== 'undefined' && submitting">{{ $loadingText }}</span>
    </button>
@endif
