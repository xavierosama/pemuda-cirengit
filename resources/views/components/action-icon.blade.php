@props([
    'href' => null,
    'action' => null,
    'method' => 'POST',
    'label',
    'icon' => 'eye',
    'variant' => 'slate',
    'confirm' => null,
    'confirmTitle' => null,
    'confirmDescription' => null,
    'confirmText' => null,
    'cancelText' => 'Batal',
    'confirmVariant' => null,
])

@php
    $variants = [
        'slate' => 'text-slate-700 hover:bg-slate-100 focus:ring-slate-500 dark:text-slate-300 dark:hover:bg-slate-800',
        'blue' => 'text-sky-700 hover:bg-sky-50 focus:ring-sky-500 dark:text-sky-300 dark:hover:bg-sky-500/10',
        'amber' => 'text-amber-700 hover:bg-amber-50 focus:ring-amber-500 dark:text-amber-300 dark:hover:bg-amber-500/10',
        'red' => 'text-red-700 hover:bg-red-50 focus:ring-red-500 dark:text-red-300 dark:hover:bg-red-500/10',
        'emerald' => 'text-emerald-700 hover:bg-emerald-50 focus:ring-emerald-500 dark:text-emerald-300 dark:hover:bg-emerald-500/10',
        'cyan' => 'text-cyan-700 hover:bg-cyan-50 focus:ring-cyan-500 dark:text-cyan-300 dark:hover:bg-cyan-500/10',
        'indigo' => 'text-indigo-700 hover:bg-indigo-50 focus:ring-indigo-500 dark:text-indigo-300 dark:hover:bg-indigo-500/10',
        'violet' => 'text-violet-700 hover:bg-violet-50 focus:ring-violet-500 dark:text-violet-300 dark:hover:bg-violet-500/10',
    ];
    $buttonClass = ($variants[$variant] ?? $variants['slate']).' group relative inline-flex h-8 w-8 items-center justify-center rounded-xl transition focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-slate-950';
    $tooltipClass = 'pointer-events-none absolute bottom-full left-1/2 z-20 mb-2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-xs font-medium text-white opacity-0 shadow-sm transition group-hover:opacity-100 group-focus:opacity-100';
    $isDelete = strtoupper($method) === 'DELETE' || $variant === 'red' || $icon === 'trash';
    $isReset = $icon === 'key' || str($label)->lower()->contains('reset');
    $isSync = str($label)->lower()->contains('sinkron');
    $resolvedTitle = $confirmTitle
        ?? ($isDelete ? 'Hapus Data?' : ($isReset ? 'Reset Password?' : ($isSync ? 'Sinkronkan Peserta Presensi?' : 'Konfirmasi Aksi')));
    $resolvedDescription = $confirmDescription
        ?? ($isDelete
            ? 'Data yang dihapus tidak dapat dikembalikan. Pastikan data ini memang sudah tidak diperlukan.'
            : ($isReset
                ? 'Password akun anggota akan direset. Informasikan password baru kepada anggota terkait.'
                : ($isSync
                    ? 'Peserta presensi akan disesuaikan dengan data anggota aktif. Data presensi yang sudah ada tetap mengikuti aturan sistem.'
                    : ($confirm ?: 'Pastikan aksi ini memang ingin dilanjutkan.'))));
    $resolvedConfirmText = $confirmText ?? ($isDelete ? 'Hapus' : ($isReset ? 'Reset Password' : ($isSync ? 'Sinkronkan' : 'Ya, lanjutkan')));
    $resolvedVariant = $confirmVariant ?? ($isDelete ? 'danger' : ($isReset || $isSync ? 'warning' : 'primary'));
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        title="{{ $label }}"
        aria-label="{{ $label }}"
        x-data="{ submitting: false }"
        x-on:click="submitting = true"
        x-bind:class="{ 'pointer-events-none opacity-70': submitting }"
        {{ $attributes->merge(['class' => $buttonClass]) }}
    >
        <span class="{{ $tooltipClass }}">{{ $label }}</span>
        <span class="sr-only">{{ $label }}</span>
        <svg x-cloak x-show="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <span x-show="! submitting">
            @include('components.partials.action-icon-svg', ['icon' => $icon])
        </span>
    </a>
@else
    @if ($confirm)
        <div x-data="{ open: false, submitting: false }" class="inline-flex" x-on:confirmed="submitting = true; $refs.confirmableAction.submit()">
            <form x-ref="confirmableAction" method="POST" action="{{ $action }}" class="inline-flex" x-on:submit.prevent="open = true">
                @csrf
                @if (strtoupper($method) !== 'POST')
                    @method($method)
                @endif
                <button type="submit" title="{{ $label }}" aria-label="{{ $label }}" x-bind:disabled="submitting" {{ $attributes->merge(['class' => $buttonClass.' disabled:cursor-not-allowed disabled:opacity-60']) }}>
                    <span class="{{ $tooltipClass }}">{{ $label }}</span>
                    <span class="sr-only">{{ $label }}</span>
                    <svg x-cloak x-show="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span x-show="! submitting">
                        @include('components.partials.action-icon-svg', ['icon' => $icon])
                    </span>
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
        <form method="POST" action="{{ $action }}" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            @if (strtoupper($method) !== 'POST')
                @method($method)
            @endif
            <button type="submit" title="{{ $label }}" aria-label="{{ $label }}" x-bind:disabled="submitting" {{ $attributes->merge(['class' => $buttonClass.' disabled:cursor-not-allowed disabled:opacity-60']) }}>
                <span class="{{ $tooltipClass }}">{{ $label }}</span>
                <span class="sr-only">{{ $label }}</span>
                <svg x-cloak x-show="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span x-show="! submitting">
                    @include('components.partials.action-icon-svg', ['icon' => $icon])
                </span>
            </button>
        </form>
    @endif
@endif
