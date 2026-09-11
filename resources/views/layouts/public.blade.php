@php
    $systemSettings = app(\App\Support\SystemSettings::class);
    $appName = $systemSettings->get('app_name');
    $organizationName = $systemSettings->get('organization_name');
    $appLogoUrl = $systemSettings->assetUrl('app_logo');
    $faviconUrl = $systemSettings->assetUrl('favicon');
    $themeMode = $systemSettings->themeMode();
    $title = trim($__env->yieldContent('title', $appName));
    $description = trim($__env->yieldContent('meta_description', 'Sistem administrasi, agenda, presensi, dan dokumentasi kajian Pemuda Persis Cirengit.'));
    $ogImage = trim($__env->yieldContent('og_image', $appLogoUrl ?: ''));
    $publicNavCategories = \App\Models\ArticleCategory::query()
        ->active()
        ->orderByRaw('sort_order is null')
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();
    $currentCategorySlug = request()->routeIs('public.categories.*')
        ? optional(request()->route('category'))->slug
        : request('category');
    $isHomeActive = request()->routeIs('public.home');
    $isArticlesActive = request()->routeIs('public.articles.*');
    $isCategoriesActive = request()->routeIs('public.categories.*') || filled($currentCategorySlug);
    $navLinkClass = fn (bool $active) => $active
        ? 'text-emerald-700'
        : 'text-slate-600 hover:text-emerald-700';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $themeMode === 'dark' ? 'dark' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }}</title>
        <meta name="description" content="{{ $description }}">
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="@yield('og_type', 'website')">
        @if ($ogImage)
            <meta property="og:image" content="{{ $ogImage }}">
        @endif

        @if ($faviconUrl)
            <link rel="icon" href="{{ $faviconUrl }}">
        @endif
        <x-pwa.meta :app-name="$appName" />

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 font-sans antialiased text-slate-900 selection:bg-emerald-100 selection:text-emerald-900">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.12),transparent_32rem),linear-gradient(135deg,#f8fafc,#ecfdf5_45%,#f8fafc)]">
            <header class="sticky top-0 z-40 w-full border-b border-white/70 bg-white/85 shadow-sm shadow-slate-200/50 backdrop-blur-xl" x-data="{ mobileMenuOpen: false, mobileCategoriesOpen: {{ $isCategoriesActive ? 'true' : 'false' }} }">
                <div class="mx-auto flex w-full max-w-7xl items-center px-4 py-3 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-8">
                        <a href="{{ route('public.home') }}" class="flex shrink-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-emerald-700 text-sm font-extrabold text-white ring-1 ring-inset ring-emerald-600/40">
                                @if ($appLogoUrl)
                                    <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-1.5">
                                @else
                                    {{ str($appName)->substr(0, 2)->upper() }}
                                @endif
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-extrabold text-slate-950 sm:text-base">{{ $appName }}</span>
                                <span class="hidden truncate text-xs font-medium text-slate-500 sm:block">{{ $organizationName }}</span>
                            </span>
                        </a>

                        <nav class="hidden items-center gap-6 text-sm font-semibold lg:flex" aria-label="Navigasi publik">
                            <a href="{{ route('public.home') }}" class="{{ $navLinkClass($isHomeActive) }}">Beranda</a>
                            <a href="{{ route('public.articles.index') }}" class="{{ $navLinkClass($isArticlesActive) }}">Artikel</a>

                            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                                <button type="button" class="{{ $navLinkClass($isCategoriesActive) }} inline-flex items-center gap-1.5" @click="open = ! open" :aria-expanded="open.toString()">
                                    <span>Kategori</span>
                                    <svg class="h-4 w-4 transition" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div x-cloak x-show="open" x-transition.origin.top.left class="absolute left-0 top-full z-50 mt-3 w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10">
                                    @forelse ($publicNavCategories as $category)
                                        <a href="{{ route('public.categories.show', $category) }}" class="{{ $currentCategorySlug === $category->slug ? 'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-emerald-700' }} block rounded-xl px-3 py-2.5 transition">
                                            <span class="block text-sm font-bold">{{ $category->name }}</span>
                                            @if ($category->description)
                                                <span class="mt-0.5 line-clamp-1 block text-xs font-medium text-slate-500">{{ $category->description }}</span>
                                            @endif
                                        </a>
                                    @empty
                                        <span class="block rounded-xl px-3 py-3 text-sm font-semibold text-slate-400">Belum ada kategori aktif</span>
                                    @endforelse
                                </div>
                            </div>

                            <a href="{{ route('public.home') }}#tentang" class="text-slate-600 hover:text-emerald-700">Tentang</a>
                        </nav>
                    </div>

                    <div class="hidden min-w-6 flex-1 lg:block" aria-hidden="true"></div>

                    <div class="ml-auto hidden shrink-0 items-center lg:flex" style="margin-left: auto;">
                        @auth
                            <a href="{{ auth()->user()->role === 'member' ? route('member.home') : route('dashboard') }}" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-bold text-white shadow-sm shadow-emerald-700/20 transition hover:bg-emerald-800">
                                Masuk
                            </a>
                        @endauth
                    </div>

                    <div class="ml-auto flex shrink-0 items-center lg:hidden" style="margin-left: auto;">
                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" @click="mobileMenuOpen = ! mobileMenuOpen" :aria-expanded="mobileMenuOpen.toString()" aria-label="Buka menu navigasi">
                            <svg x-show="! mobileMenuOpen" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                            </svg>
                            <svg x-cloak x-show="mobileMenuOpen" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div x-cloak x-show="mobileMenuOpen" x-transition class="border-t border-slate-200 bg-white/95 px-4 py-3 shadow-lg shadow-slate-900/5 lg:hidden">
                    <div class="mx-auto max-w-7xl space-y-1 text-sm font-bold">
                        <a href="{{ route('public.home') }}" class="{{ $isHomeActive ? 'bg-emerald-50 text-emerald-800' : 'text-slate-700 hover:bg-slate-50' }} block rounded-xl px-3 py-2.5">Beranda</a>
                        <a href="{{ route('public.articles.index') }}" class="{{ $isArticlesActive ? 'bg-emerald-50 text-emerald-800' : 'text-slate-700 hover:bg-slate-50' }} block rounded-xl px-3 py-2.5">Artikel</a>

                        <div>
                            <button type="button" class="{{ $isCategoriesActive ? 'bg-emerald-50 text-emerald-800' : 'text-slate-700 hover:bg-slate-50' }} flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left" @click="mobileCategoriesOpen = ! mobileCategoriesOpen" :aria-expanded="mobileCategoriesOpen.toString()">
                                <span>Kategori</span>
                                <svg class="h-4 w-4 transition" :class="{ 'rotate-180': mobileCategoriesOpen }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-cloak x-show="mobileCategoriesOpen" x-transition class="mt-1 max-h-72 space-y-1 overflow-y-auto rounded-2xl bg-slate-50 p-2">
                                @forelse ($publicNavCategories as $category)
                                    <a href="{{ route('public.categories.show', $category) }}" class="{{ $currentCategorySlug === $category->slug ? 'bg-white text-emerald-800 ring-1 ring-inset ring-emerald-100' : 'text-slate-600 hover:bg-white hover:text-emerald-700' }} block rounded-xl px-3 py-2.5">
                                        {{ $category->name }}
                                    </a>
                                @empty
                                    <span class="block rounded-xl px-3 py-2.5 text-slate-400">Belum ada kategori aktif</span>
                                @endforelse
                            </div>
                        </div>

                        <a href="{{ route('public.home') }}#tentang" class="block rounded-xl px-3 py-2.5 text-slate-700 hover:bg-slate-50">Tentang</a>

                        @auth
                            <a href="{{ auth()->user()->role === 'member' ? route('member.home') : route('dashboard') }}" class="mt-2 block rounded-xl bg-emerald-700 px-3 py-2.5 text-center text-white shadow-sm shadow-emerald-700/20">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="mt-2 block rounded-xl bg-emerald-700 px-3 py-2.5 text-center text-white shadow-sm shadow-emerald-700/20">Masuk</a>
                        @endauth
                    </div>
                </div>
            </header>

            <main>
                @yield('content')
            </main>

            <footer class="border-t border-slate-200 bg-white/80">
                <div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 md:grid-cols-[1fr_auto] lg:px-8">
                    <div>
                        <p class="text-base font-extrabold text-slate-950">{{ $appName }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $organizationName }}</p>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">Dokumentasi kajian, agenda, dan administrasi organisasi dalam satu rumah digital yang ringan dan mudah diakses.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-sm font-semibold text-slate-600 md:justify-end">
                        <a href="{{ route('public.articles.index') }}" class="hover:text-emerald-700">Artikel</a>
                        <a href="{{ route('login') }}" class="hover:text-emerald-700">Login</a>
                        <span class="text-slate-300">&copy; {{ now()->year }}</span>
                    </div>
                </div>
            </footer>
        </div>
        <x-pwa.register />
        <x-ui.toast />
        @stack('scripts')
    </body>
</html>
