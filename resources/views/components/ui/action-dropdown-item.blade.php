@props([
    'href' => null,
    'action' => null,
    'click' => null,
    'method' => 'POST',
    'label',
    'icon' => 'eye',
    'variant' => 'default',
    'confirm' => null,
    'confirmTitle' => null,
    'confirmDescription' => null,
    'confirmText' => null,
    'cancelText' => 'Batal',
    'confirmVariant' => null,
])

@php
    $isDanger = $variant === 'danger' || strtoupper($method) === 'DELETE' || $icon === 'trash';
    $toneClass = match (true) {
        $isDanger => 'text-red-700 hover:bg-red-50 focus:bg-red-50 dark:text-red-300 dark:hover:bg-red-500/10 dark:focus:bg-red-500/10',
        $variant === 'warning' => 'text-amber-700 hover:bg-amber-50 focus:bg-amber-50 dark:text-amber-300 dark:hover:bg-amber-500/10 dark:focus:bg-amber-500/10',
        default => 'text-slate-700 hover:bg-slate-50 focus:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus:bg-slate-800',
    };
    $itemClass = $toneClass
        .' flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-semibold transition focus:outline-none';

    $resolvedTitle = $confirmTitle ?? ($isDanger ? 'Hapus Data?' : 'Konfirmasi Aksi');
    $resolvedDescription = $confirmDescription ?? ($confirm ?: 'Pastikan aksi ini memang ingin dilanjutkan.');
    $resolvedConfirmText = $confirmText ?? ($isDanger ? 'Hapus' : 'Ya, lanjutkan');
    $resolvedVariant = $confirmVariant ?? ($isDanger ? 'danger' : 'primary');
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        title="{{ $label }}"
        aria-label="{{ $label }}"
        class="{{ $itemClass }}"
        x-on:click="dropdownOpen = false"
    >
        @include('components.partials.action-icon-svg', ['icon' => $icon])
        <span>{{ $label }}</span>
    </a>
@elseif ($click)
    <button
        type="button"
        title="{{ $label }}"
        aria-label="{{ $label }}"
        class="{{ $itemClass }}"
        x-on:click="{{ $click }}; dropdownOpen = false"
    >
        @include('components.partials.action-icon-svg', ['icon' => $icon])
        <span>{{ $label }}</span>
    </button>
@elseif ($confirm)
    <div x-data="{ open: false, submitting: false }" x-on:confirmed="submitting = true; $refs.dropdownAction.submit()">
        <form x-ref="dropdownAction" method="POST" action="{{ $action }}" x-on:submit.prevent="open = true; dropdownOpen = false">
            @csrf
            @if (strtoupper($method) !== 'POST')
                @method($method)
            @endif
            <button
                type="submit"
                title="{{ $label }}"
                aria-label="{{ $label }}"
                class="{{ $itemClass }} disabled:cursor-not-allowed disabled:opacity-70"
                x-bind:disabled="submitting"
            >
                @include('components.partials.action-icon-svg', ['icon' => $icon])
                <span>{{ $label }}</span>
            </button>
        </form>

        <x-ui.confirm-modal
            :title="$resolvedTitle"
            :description="$resolvedDescription"
            :confirm-text="$resolvedConfirmText"
            :cancel-text="$cancelText"
            :variant="$resolvedVariant"
        />
    </div>
@else
    <form method="POST" action="{{ $action }}" x-data="{ submitting: false }" x-on:submit="submitting = true; dropdownOpen = false">
        @csrf
        @if (strtoupper($method) !== 'POST')
            @method($method)
        @endif
        <button
            type="submit"
            title="{{ $label }}"
            aria-label="{{ $label }}"
            class="{{ $itemClass }} disabled:cursor-not-allowed disabled:opacity-70"
            x-bind:disabled="submitting"
        >
            @include('components.partials.action-icon-svg', ['icon' => $icon])
            <span>{{ $label }}</span>
        </button>
    </form>
@endif
