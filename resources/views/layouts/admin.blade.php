<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Pemuda Cirengit'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @php
            $menus = [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
                ['label' => 'Data Anggota', 'href' => '#', 'active' => false],
                ['label' => 'Data Bidang', 'href' => route('departments.index'), 'active' => request()->routeIs('departments.*')],
                ['label' => 'Data Jabatan', 'href' => '#', 'active' => false],
                ['label' => 'Jadwal Agenda', 'href' => '#', 'active' => false],
                ['label' => 'Kegiatan Aktual', 'href' => '#', 'active' => false],
                ['label' => 'Daftar Hadir', 'href' => '#', 'active' => false],
            ];
        @endphp

        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-slate-100">
            <aside
                class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-slate-200 bg-white transition duration-200 ease-in-out lg:translate-x-0"
                :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': ! sidebarOpen }"
            >
                <div class="flex h-16 items-center justify-between border-b border-slate-200 px-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-700 text-sm font-bold text-white">
                            PC
                        </div>
                        <div>
                            <div class="text-sm font-bold uppercase tracking-wide text-slate-900">Pemuda Cirengit</div>
                            <div class="text-xs font-medium text-slate-500">Administrasi Internal</div>
                        </div>
                    </a>

                    <button
                        type="button"
                        class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 lg:hidden"
                        @click="sidebarOpen = false"
                        aria-label="Tutup menu"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <nav class="space-y-1 px-4 py-6">
                    @foreach ($menus as $menu)
                        <a
                            href="{{ $menu['href'] }}"
                            class="{{ $menu['active'] ? 'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} flex items-center rounded-lg px-3 py-2.5 text-sm font-semibold transition"
                        >
                            <span class="{{ $menu['active'] ? 'bg-emerald-700' : 'bg-slate-300' }} mr-3 h-2 w-2 rounded-full"></span>
                            {{ $menu['label'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"
                @click="sidebarOpen = false"
            ></div>

            <div class="min-h-screen lg:pl-72">
                <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
                    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 lg:hidden"
                                @click="sidebarOpen = true"
                                aria-label="Buka menu"
                            >
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">@yield('section', 'Admin')</p>
                                <h1 class="text-lg font-bold text-slate-900">@yield('page-title', 'Dashboard')</h1>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="hidden text-right sm:block">
                                <div class="text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-slate-500">{{ Auth::user()->email }}</div>
                            </div>

                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-bold uppercase text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                                        {{ str(Auth::user()->name)->substr(0, 1) }}
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
                                    </x-dropdown-link>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </header>

                <main class="px-4 py-6 sm:px-6 lg:px-8">
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
