@props([
    'status',
    'label' => null,
])

@php
    $labels = [
        'active' => 'Aktif',
        'inactive' => 'Tidak Aktif',
        'present' => 'Hadir',
        'permission' => 'Izin',
        'absent' => 'Tidak Hadir',
        'need_verification' => 'Perlu Verifikasi',
        'valid' => 'Valid',
        'rejected' => 'Ditolak',
        'scheduled' => 'Terjadwal',
        'completed' => 'Selesai',
        'postponed' => 'Ditunda',
        'relocated' => 'Dipindah',
        'cancelled' => 'Dibatalkan',
        'holiday' => 'Libur',
        'moved' => 'Pindah',
        'alumni' => 'Alumni',
        'account_exists' => 'Sudah Ada',
        'account_missing' => 'Belum Ada',
    ];

    $classes = [
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'inactive' => 'bg-slate-100 text-slate-600 ring-slate-200',
        'present' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'permission' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'absent' => 'bg-slate-100 text-slate-600 ring-slate-200',
        'need_verification' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'valid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'rejected' => 'bg-red-50 text-red-700 ring-red-200',
        'scheduled' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'postponed' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'relocated' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',
        'cancelled' => 'bg-red-50 text-red-700 ring-red-200',
        'holiday' => 'bg-slate-100 text-slate-600 ring-slate-200',
        'moved' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'alumni' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'account_exists' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'account_missing' => 'bg-amber-50 text-amber-700 ring-amber-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => ($classes[$status] ?? $classes['inactive']).' inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset']) }}>
    {{ $label ?? $labels[$status] ?? str($status)->headline() }}
</span>
