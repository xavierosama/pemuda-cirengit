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
        'slate' => 'text-slate-700 hover:bg-slate-100 focus:ring-slate-500',
        'blue' => 'text-blue-700 hover:bg-blue-50 focus:ring-blue-500',
        'amber' => 'text-amber-700 hover:bg-amber-50 focus:ring-amber-500',
        'red' => 'text-red-700 hover:bg-red-50 focus:ring-red-500',
        'emerald' => 'text-emerald-700 hover:bg-emerald-50 focus:ring-emerald-500',
        'cyan' => 'text-cyan-700 hover:bg-cyan-50 focus:ring-cyan-500',
        'indigo' => 'text-indigo-700 hover:bg-indigo-50 focus:ring-indigo-500',
        'violet' => 'text-violet-700 hover:bg-violet-50 focus:ring-violet-500',
    ];
    $buttonClass = ($variants[$variant] ?? $variants['slate']).' group relative inline-flex h-8 w-8 items-center justify-center rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-2';
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
    <a href="{{ $href }}" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes->merge(['class' => $buttonClass]) }}>
        <span class="{{ $tooltipClass }}">{{ $label }}</span>
        <span class="sr-only">{{ $label }}</span>
        @include('components.partials.action-icon-svg', ['icon' => $icon])
    </a>
@else
    @if ($confirm)
        <div x-data="{ open: false }" class="inline-flex" x-on:confirmed="$refs.confirmableAction.submit()">
            <form x-ref="confirmableAction" method="POST" action="{{ $action }}" class="inline-flex" x-on:submit.prevent="open = true">
                @csrf
                @if (strtoupper($method) !== 'POST')
                    @method($method)
                @endif
                <button type="submit" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes->merge(['class' => $buttonClass]) }}>
                    <span class="{{ $tooltipClass }}">{{ $label }}</span>
                    <span class="sr-only">{{ $label }}</span>
                    @include('components.partials.action-icon-svg', ['icon' => $icon])
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
        <form method="POST" action="{{ $action }}" class="inline-flex">
            @csrf
            @if (strtoupper($method) !== 'POST')
                @method($method)
            @endif
            <button type="submit" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes->merge(['class' => $buttonClass]) }}>
                <span class="{{ $tooltipClass }}">{{ $label }}</span>
                <span class="sr-only">{{ $label }}</span>
                @include('components.partials.action-icon-svg', ['icon' => $icon])
            </button>
        </form>
    @endif
@endif
