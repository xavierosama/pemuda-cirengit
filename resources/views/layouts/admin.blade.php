@php
    $systemSettings = app(\App\Support\SystemSettings::class);
    $appName = $systemSettings->get('app_name');
    $organizationName = $systemSettings->get('organization_name');
    $appLogoUrl = $systemSettings->assetUrl('app_logo');
    $faviconUrl = $systemSettings->assetUrl('favicon');
    $themeMode = $systemSettings->themeMode();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $themeMode === 'dark' ? 'dark' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', $appName)</title>

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
        @php
            $menuSections = [
                [
                    'heading' => null,
                    'items' => [
                        ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard'), 'icon' => 'dashboard'],
                    ],
                ],
                [
                    'heading' => 'Master Data',
                    'items' => [
                        ['label' => 'Data Anggota', 'href' => route('members.index'), 'active' => request()->routeIs('members.*'), 'icon' => 'users'],
                        ['label' => 'Data Bidang', 'href' => route('departments.index'), 'active' => request()->routeIs('departments.*'), 'icon' => 'folder'],
                        ['label' => 'Data Jabatan', 'href' => route('positions.index'), 'active' => request()->routeIs('positions.*'), 'icon' => 'badge'],
                    ],
                ],
                [
                    'heading' => 'Agenda & Kegiatan',
                    'items' => [
                        ['label' => 'Jadwal Agenda', 'href' => route('agenda-schedules.index'), 'active' => request()->routeIs('agenda-schedules.*'), 'icon' => 'calendar'],
                        ['label' => 'Kegiatan Aktual', 'href' => route('activities.index'), 'active' => request()->routeIs('activities.*') && ! request()->routeIs('activities.attendances.*'), 'icon' => 'sparkles'],
                    ],
                ],
                [
                    'heading' => 'Presensi',
                    'items' => [
                        ['label' => 'Daftar Hadir', 'href' => route('attendances.index'), 'active' => request()->routeIs('attendances.*') || request()->routeIs('activities.attendances.*'), 'icon' => 'clipboard'],
                        ['label' => 'Rekap Presensi', 'href' => route('attendance-reports.index'), 'active' => request()->routeIs('attendance-reports.*'), 'icon' => 'chart'],
                    ],
                ],
                [
                    'heading' => 'Pengaturan',
                    'items' => [
                        ['label' => 'Pengaturan Sistem', 'href' => route('settings.edit'), 'active' => request()->routeIs('settings.*'), 'icon' => 'cog'],
                    ],
                ],
            ];
        @endphp

        @php
            $authUser = Auth::user();
            $displayName = $authUser?->member?->full_name ?? $authUser?->name ?? 'User';
            $initial = str($displayName)->substr(0, 1)->upper();
            $profilePhotoUrl = $authUser?->member?->profile_photo ? asset('storage/'.$authUser->member->profile_photo) : null;
        @endphp

        <div
            x-data="{
                sidebarOpen: false,
                sidebarCollapsed: localStorage.getItem('pemuda-sidebar-collapsed') === 'true',
                toggleSidebarCollapse() {
                    this.sidebarCollapsed = ! this.sidebarCollapsed;
                    localStorage.setItem('pemuda-sidebar-collapsed', this.sidebarCollapsed ? 'true' : 'false');
                }
            }"
            class="min-h-screen bg-slate-100 dark:bg-slate-950"
        >
            <aside
                class="fixed inset-y-0 left-0 z-40 -translate-x-full border-r border-slate-200 bg-white shadow-xl shadow-slate-200/50 transition-all duration-200 ease-in-out dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 lg:translate-x-0 lg:shadow-none"
                :class="{
                    'translate-x-0 w-72': sidebarOpen,
                    '-translate-x-full w-72': ! sidebarOpen,
                    'lg:w-20': sidebarCollapsed,
                    'lg:w-72': ! sidebarCollapsed
                }"
            >
                <div class="flex h-16 items-center justify-between border-b border-slate-200 px-4 dark:border-slate-800" :class="sidebarCollapsed ? 'lg:justify-center' : ''">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-emerald-700 text-sm font-bold text-white ring-1 ring-inset ring-emerald-600/40">
                            @if ($appLogoUrl)
                                <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-1.5">
                            @else
                                {{ str($appName)->substr(0, 2)->upper() }}
                            @endif
                        </div>
                        <div x-show="! sidebarCollapsed" x-transition.opacity class="min-w-0 lg:block">
                            <div class="max-w-44 truncate text-sm font-bold text-slate-950 dark:text-white">{{ $appName }}</div>
                            <div class="max-w-44 truncate text-xs font-medium text-slate-500 dark:text-slate-400">{{ $organizationName }}</div>
                        </div>
                    </a>

                    <button
                        type="button"
                        class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200 lg:hidden"
                        @click="sidebarOpen = false"
                        aria-label="Tutup menu"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <nav class="space-y-6 px-3 py-5">
                    @foreach ($menuSections as $section)
                        <div class="space-y-1.5">
                            @if ($section['heading'])
                                <p x-show="! sidebarCollapsed" x-transition.opacity class="px-3 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $section['heading'] }}</p>
                            @endif

                            @foreach ($section['items'] as $menu)
                                <a
                                    href="{{ $menu['href'] }}"
                                    title="{{ $menu['label'] }}"
                                    aria-label="{{ $menu['label'] }}"
                                    class="{{ $menu['active'] ? 'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-400/20' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/70 dark:hover:text-white' }} group relative flex items-center rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                                >
                                    <span class="{{ $menu['active'] ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' }} shrink-0 transition">
                                        @include('layouts.partials.admin-menu-icon', ['icon' => $menu['icon']])
                                    </span>
                                    <span x-show="! sidebarCollapsed" x-transition.opacity class="ml-3 whitespace-nowrap">{{ $menu['label'] }}</span>
                                    <span x-show="sidebarCollapsed" class="pointer-events-none absolute left-full z-50 ml-3 hidden whitespace-nowrap rounded-lg bg-slate-950 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100 group-focus:opacity-100 lg:block">{{ $menu['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </nav>
            </aside>

            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"
                @click="sidebarOpen = false"
            ></div>

            <div class="min-h-screen transition-all duration-200" :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'">
                <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/85 shadow-sm shadow-slate-200/50 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/85 dark:shadow-black/20">
                    <div class="flex min-h-16 items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200 lg:hidden"
                                @click="sidebarOpen = true"
                                aria-label="Buka menu"
                            >
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                class="hidden rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200 lg:inline-flex"
                                @click="toggleSidebarCollapse()"
                                :aria-label="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
                                :title="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
                            >
                                <svg class="h-5 w-5 transition" :class="{ 'rotate-180': sidebarCollapsed }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.25 8.5 12l7.25-7.25" />
                                </svg>
                            </button>

                            <a href="{{ route('dashboard') }}" class="hidden items-center gap-3 rounded-2xl px-1 py-1 sm:flex lg:hidden xl:flex">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-emerald-700 text-xs font-bold text-white ring-1 ring-inset ring-emerald-600/40">
                                    @if ($appLogoUrl)
                                        <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-1.5">
                                    @else
                                        {{ str($appName)->substr(0, 2)->upper() }}
                                    @endif
                                </div>
                                <div class="hidden min-w-0 xl:block">
                                    <p class="max-w-48 truncate text-sm font-bold text-slate-950 dark:text-white">{{ $appName }}</p>
                                    <p class="max-w-48 truncate text-xs text-slate-500 dark:text-slate-400">{{ $organizationName }}</p>
                                </div>
                            </a>

                            <div class="hidden h-8 w-px bg-slate-200 dark:bg-slate-800 sm:block lg:hidden xl:block"></div>

                            <div class="min-w-0">
                                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">@yield('section', 'Admin')</p>
                                <h1 class="truncate text-base font-bold text-slate-950 dark:text-white sm:text-lg">@yield('page-title', 'Dashboard')</h1>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="hidden text-right sm:block">
                                <div class="text-sm font-semibold text-slate-950 dark:text-white">{{ $displayName }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $authUser->email }}</div>
                            </div>

                            <x-dropdown align="right" width="w-56" contentClasses="overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl dark:border-slate-800 dark:bg-slate-900">
                                <x-slot name="trigger">
                                    <button class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-emerald-700 text-sm font-bold uppercase text-white shadow-sm ring-1 ring-inset ring-emerald-800 transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                                        @if ($profilePhotoUrl)
                                            <img src="{{ $profilePhotoUrl }}" alt="Foto profil {{ $displayName }}" class="h-full w-full object-cover">
                                        @else
                                            {{ $initial }}
                                        @endif
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                                        <p class="truncate text-sm font-bold text-slate-950 dark:text-white">{{ $displayName }}</p>
                                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $authUser->email }}</p>
                                    </div>
                                    <x-dropdown-link :href="route('profile.edit')">
                                        Profile
                                    </x-dropdown-link>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                            Logout
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </header>

                <main class="px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
                    <div class="mx-auto w-full max-w-7xl space-y-4">
                        @hasSection('breadcrumb')
                            @yield('breadcrumb')
                        @else
                            <x-ui.breadcrumb :items="[
                                ['label' => 'Dashboard', 'url' => route('dashboard')],
                                ['label' => trim($__env->yieldContent('page-title', 'Dashboard'))],
                            ]" />
                        @endif
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
        <x-ui.toast />
    </body>
</html>
