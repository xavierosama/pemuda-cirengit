@props([
    'href' => null,
    'action' => null,
    'method' => 'POST',
    'label',
    'variant' => 'detail',
    'icon' => null,
    'confirm' => null,
    'confirmTitle' => null,
    'confirmDescription' => null,
    'confirmText' => null,
    'cancelText' => 'Batal',
    'confirmVariant' => null,
])

@php
    $variantMap = [
        'detail' => ['color' => 'blue', 'icon' => 'eye'],
        'edit' => ['color' => 'amber', 'icon' => 'pencil'],
        'delete' => ['color' => 'red', 'icon' => 'trash'],
        'qr' => ['color' => 'indigo', 'icon' => 'qr'],
        'export' => ['color' => 'emerald', 'icon' => 'download'],
        'reset' => ['color' => 'violet', 'icon' => 'key'],
        'account' => ['color' => 'emerald', 'icon' => 'user-plus'],
    ];

    $mapped = $variantMap[$variant] ?? ['color' => $variant, 'icon' => $icon ?? 'eye'];
@endphp

<x-action-icon
    :href="$href"
    :action="$action"
    :method="$method"
    :label="$label"
    :icon="$icon ?? $mapped['icon']"
    :variant="$mapped['color']"
    :confirm="$confirm"
    :confirm-title="$confirmTitle"
    :confirm-description="$confirmDescription"
    :confirm-text="$confirmText"
    :cancel-text="$cancelText"
    :confirm-variant="$confirmVariant"
    {{ $attributes }}
/>
