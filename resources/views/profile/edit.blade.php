@php
    $isInternalUser = in_array($user->role, ['admin', 'secretary'], true);
    $systemSettings = app(\App\Support\SystemSettings::class);
    $appName = $systemSettings->get('app_name');
    $appLogoUrl = $systemSettings->assetUrl('app_logo');
    $faviconUrl = $systemSettings->assetUrl('favicon');
    $themeMode = $systemSettings->themeMode();
@endphp

@if ($isInternalUser)
    @extends('layouts.admin')

    @section('title', 'Edit Profil - '.$appName)
    @section('section', 'Pengaturan')
    @section('page-title', 'Edit Profil')
    @section('breadcrumb')
        <x-ui.breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Edit Profil'],
        ]" />
    @endsection

    @section('content')
        @include('profile.partials.profile-page-content', ['backRoute' => route('dashboard'), 'backLabel' => 'Kembali ke Dashboard Admin'])
    @endsection
@else
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $themeMode === 'dark' ? 'dark' : '' }}">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="csrf-token" content="{{ csrf_token() }}">

            <title>Edit Profil - {{ $appName }}</title>

            @if ($faviconUrl)
                <link rel="icon" href="{{ $faviconUrl }}">
            @endif

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
            <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <div class="mx-auto flex h-14 max-w-5xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <a href="{{ route('member.home') }}" class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-xl bg-emerald-700 text-xs font-bold text-white">
                            @if ($appLogoUrl)
                                <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-1.5">
                            @else
                                {{ str($appName)->substr(0, 2)->upper() }}
                            @endif
                        </span>
                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $appName }}</span>
                    </a>
                    <span class="max-w-40 truncate text-xs font-semibold text-slate-500 sm:max-w-none sm:text-sm">{{ $user->name }}</span>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-5xl space-y-4">
                    <x-ui.breadcrumb :items="[
                        ['label' => 'Dashboard Anggota', 'url' => route('member.home')],
                        ['label' => 'Edit Profil'],
                    ]" />
                    @include('profile.partials.profile-page-content', ['backRoute' => route('member.home'), 'backLabel' => 'Kembali ke Dashboard Anggota'])
                </div>
            </main>
        </body>
    </html>
@endif
