@props([
    'title' => 'Konfirmasi Aksi',
    'description' => null,
    'confirmText' => 'Ya, lanjutkan',
    'cancelText' => 'Batal',
    'variant' => 'primary',
    'loadingText' => 'Memproses...',
])

@php
    $variantClasses = [
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-600',
        'warning' => 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-500',
        'primary' => 'bg-emerald-700 text-white hover:bg-emerald-800 focus:ring-emerald-600',
    ];

    $iconClasses = [
        'danger' => 'bg-red-50 text-red-600 ring-red-100',
        'warning' => 'bg-amber-50 text-amber-600 ring-amber-100',
        'primary' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
    ];
@endphp

<div
    x-cloak
    x-show="open"
    x-on:keydown.escape.window="open = false"
    class="fixed inset-0 z-[60] flex min-h-screen items-center justify-center px-4 py-6"
    role="dialog"
    aria-modal="true"
    style="display: none;"
>
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"
        x-on:click="open = false"
        aria-hidden="true"
    ></div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-3 scale-95 opacity-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
        x-transition:leave-end="translate-y-3 scale-95 opacity-0"
        class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl shadow-slate-950/20"
    >
        <div class="flex gap-4">
            <div class="{{ $iconClasses[$variant] ?? $iconClasses['primary'] }} flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ring-1 ring-inset">
                @if ($variant === 'danger')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v4.5M12 16.25h.01M10.2 4.75h3.6L21 18.5a1.5 1.5 0 0 1-1.35 2.25H4.35A1.5 1.5 0 0 1 3 18.5L10.2 4.75Z" />
                    </svg>
                @else
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15.5 9.5M20.25 12A8.25 8.25 0 1 1 3.75 12a8.25 8.25 0 0 1 16.5 0Z" />
                    </svg>
                @endif
            </div>

            <div class="min-w-0">
                <h2 class="text-lg font-bold text-slate-950">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $description }}</p>
                @endif
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70"
                x-on:click="open = false"
                x-bind:disabled="typeof submitting !== 'undefined' && submitting"
            >
                {{ $cancelText }}
            </button>
            <button
                type="button"
                class="{{ $variantClasses[$variant] ?? $variantClasses['primary'] }} inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-80"
                x-on:click="$dispatch('confirmed')"
                x-bind:disabled="typeof submitting !== 'undefined' && submitting"
            >
                <svg x-cloak x-show="typeof submitting !== 'undefined' && submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-90" fill="currentColor" d="M21 12a9 9 0 0 0-9-9v4a5 5 0 0 1 5 5h4Z"></path>
                </svg>
                <span x-show="!(typeof submitting !== 'undefined' && submitting)">{{ $confirmText }}</span>
                <span x-cloak x-show="typeof submitting !== 'undefined' && submitting">{{ $loadingText }}</span>
            </button>
        </div>
    </div>
</div>
