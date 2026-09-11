@php
    $systemSettings = app(\App\Support\SystemSettings::class);
    $appName = $systemSettings->get('app_name');
    $organizationName = $systemSettings->get('organization_name');
    $appLogoUrl = $systemSettings->assetUrl('app_logo');
    $faviconUrl = $systemSettings->assetUrl('favicon');
    $themeMode = $systemSettings->themeMode();
    $monthOptions = collect(range(1, 12))->mapWithKeys(fn ($item) => [$item => \Carbon\Carbon::create(null, $item, 1)->translatedFormat('F')]);
    $currentStatus = $currentRecord?->displayStatus() ?? 'not_available';
    $currentStatusLabel = $currentRecord?->displayStatusLabel() ?? 'Belum tercatat';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $themeMode === 'dark' ? 'dark' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Iuran Saya - {{ $appName }}</title>
        @if ($faviconUrl)
            <link rel="icon" href="{{ $faviconUrl }}">
        @endif
        <x-pwa.meta :app-name="$appName" />
        <script>
            (() => {
                const themeMode = @json($themeMode);
                if (themeMode === 'dark' || (themeMode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 font-sans antialiased text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/35 to-slate-100 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 shadow-sm shadow-slate-200/50 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
                <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-2 sm:px-6 sm:py-2.5 lg:px-8">
                    <a href="{{ route('member.home') }}" class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-emerald-700 text-xs font-bold text-white">
                            @if ($appLogoUrl)
                                <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-1.5">
                            @else
                                {{ str($appName)->substr(0, 2)->upper() }}
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="max-w-40 truncate text-sm font-bold text-slate-950 dark:text-white sm:max-w-none">{{ $appName }}</p>
                            <p class="hidden text-xs text-slate-500 dark:text-slate-400 sm:block">Iuran Saya</p>
                        </div>
                    </a>
                    <div class="flex items-center gap-2">
                        <x-member.notifications-menu />
                        <x-member.account-menu :user="$user" :member="$member" />
                    </div>
                </div>
            </header>

            <main class="px-4 py-4 sm:px-6 sm:py-6 lg:px-8">
                <div class="mx-auto max-w-5xl space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">{{ $organizationName }}</p>
                            <h1 class="mt-2 text-2xl font-bold text-slate-950 sm:text-3xl">Iuran Saya</h1>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Halaman ini hanya untuk melihat status iuran pribadi. Pembayaran tetap mengikuti arahan bendahara/pengurus.</p>
                        </div>
                        <form method="GET" action="{{ route('member.fees.index') }}" class="flex items-center gap-2">
                            <label for="year" class="sr-only">Tahun</label>
                            <input id="year" name="year" type="number" min="2020" max="2100" value="{{ $year }}" class="w-28 rounded-xl border-slate-300 text-sm">
                            <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-bold text-white">Tampilkan</button>
                        </form>
                    </div>

                    <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <x-ui.card padding="sm" class="sm:col-span-2">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Status Bulan Ini</p>
                            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-2xl font-bold text-slate-950">{{ $monthOptions[now()->month] }} {{ now()->year }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Nominal berlaku: Rp {{ number_format($summary['default_amount'], 0, ',', '.') }}</p>
                                </div>
                                <x-ui.status-badge :status="$currentStatus" :label="$currentStatusLabel" />
                            </div>
                        </x-ui.card>
                        <x-ui.card padding="sm">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Lunas</p>
                            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $summary['paid'] }}</p>
                        </x-ui.card>
                        <x-ui.card padding="sm">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Belum Bayar</p>
                            <p class="mt-2 text-2xl font-bold text-amber-700">{{ $summary['unpaid'] }}</p>
                        </x-ui.card>
                    </section>

                    <x-ui.card padding="md">
                        <div class="flex flex-col gap-1 border-b border-slate-100 pb-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-slate-950">Timeline Iuran {{ $year }}</h2>
                                <p class="mt-1 text-sm text-slate-500">Status bulanan yang sudah dicatat oleh bendahara.</p>
                            </div>
                            <p class="text-sm font-semibold text-slate-500">Dibebaskan: {{ $summary['waived'] }} bulan</p>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($monthOptions as $monthNumber => $monthName)
                                @php
                                    $record = $records->get($monthNumber);
                                    $status = $record?->displayStatus() ?? 'not_available';
                                    $label = $record?->displayStatusLabel() ?? 'Belum tercatat';
                                @endphp
                                <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="font-bold text-slate-950">{{ $monthName }}</h3>
                                            <p class="mt-1 text-sm text-slate-500">Rp {{ number_format($record?->amount ?? $summary['default_amount'], 0, ',', '.') }}</p>
                                        </div>
                                        <x-ui.status-badge :status="$status" :label="$label" />
                                    </div>
                                    @if ($record?->paid_at)
                                        <p class="mt-3 text-sm text-slate-600">Dicatat lunas: {{ \App\Support\DateFormatter::date($record->paid_at) }}</p>
                                    @endif
                                    @if ($record?->note)
                                        <p class="mt-2 line-clamp-2 text-sm text-slate-500">{{ $record->note }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </x-ui.card>

                    <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-800">
                        <p class="font-bold">Informasi</p>
                        <p class="mt-1 leading-6">Tidak ada fitur bayar online atau upload bukti pada halaman ini. Jika ada perbedaan catatan, silakan hubungi bendahara/pengurus.</p>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
