@props([
    'padding' => 'md',
    'accent' => null,
])

@php
    $paddingClasses = [
        'none' => '',
        'sm' => 'p-4',
        'md' => 'p-5',
        'lg' => 'p-6',
    ];

    $accentClasses = [
        'emerald' => 'border-l-4 border-l-emerald-500',
        'sky' => 'border-l-4 border-l-sky-500',
        'amber' => 'border-l-4 border-l-amber-500',
        'red' => 'border-l-4 border-l-red-500',
        'violet' => 'border-l-4 border-l-violet-500',
        'slate' => 'border-l-4 border-l-slate-400',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-200/60 ring-1 ring-white/70 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/10 dark:ring-slate-800/60 '.($accent ? ($accentClasses[$accent] ?? '') : '').' '.($paddingClasses[$padding] ?? $paddingClasses['md'])]) }}>
    {{ $slot }}
</div>
