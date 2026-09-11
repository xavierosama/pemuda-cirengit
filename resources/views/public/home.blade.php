@extends('layouts.public')

@section('title', 'Pemuda Cirengit - Kajian, Agenda, dan Presensi')
@section('meta_description', 'Wajah publik Pemuda Persis Cirengit untuk dokumentasi kajian, agenda, presensi, dan administrasi organisasi.')

@section('content')
    <section class="relative overflow-hidden">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-20">
            <div class="flex flex-col justify-center">
                <p class="inline-flex w-fit rounded-full bg-emerald-50 px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-700 ring-1 ring-inset ring-emerald-200">Pemuda Persis Cirengit</p>
                <h1 class="mt-5 max-w-3xl text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                    Kajian, agenda, dan presensi dalam satu ruang digital.
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                    Dokumentasi hasil kajian keilmuan Pemuda Persis Cirengit yang rapi, mudah dibaca, dan terhubung dengan sistem administrasi organisasi.
                </p>
                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <a href="#artikel-terbaru" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 text-sm font-extrabold text-white shadow-sm shadow-emerald-700/20 transition hover:bg-emerald-800">Lihat Artikel Terbaru</a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-5 py-3 text-sm font-extrabold text-emerald-700 shadow-sm transition hover:bg-emerald-50">Login Member</a>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -right-10 -top-10 h-52 w-52 rounded-full bg-emerald-300/30 blur-3xl"></div>
                <div class="relative overflow-hidden rounded-[2rem] border border-white/80 bg-white/80 p-4 shadow-2xl shadow-emerald-900/10 backdrop-blur">
                    <div class="rounded-[1.5rem] bg-gradient-to-br from-emerald-900 via-emerald-800 to-teal-900 p-6 text-white">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-100">Hasil Kajian Keilmuan</p>
                        <div class="mt-8 space-y-4">
                            @forelse (($featuredArticles->isNotEmpty() ? $featuredArticles : $latestArticles)->take(3) as $article)
                                <a href="{{ route('public.articles.show', $article) }}" class="block rounded-2xl border border-white/10 bg-white/10 p-4 transition hover:bg-white/15">
                                    <p class="text-xs font-semibold text-emerald-100">{{ $article->category?->name ?? 'Kajian' }}</p>
                                    <h2 class="mt-1 line-clamp-2 text-lg font-extrabold">{{ $article->title }}</h2>
                                    <p class="mt-2 text-xs text-emerald-50">{{ $article->reading_minutes }} menit baca · {{ number_format($article->views_count) }} views</p>
                                </a>
                            @empty
                                <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                                    <h2 class="text-xl font-extrabold">Belum ada kajian dipublikasikan.</h2>
                                    <p class="mt-2 text-sm leading-6 text-emerald-50">Kajian terbaru akan tampil di sini setelah admin mempublikasikan artikel.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="artikel-terbaru" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-700">Artikel Terbaru</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-950 sm:text-3xl">Publikasi terbaru</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Artikel dan hasil kajian yang sudah dipublikasikan oleh pengurus atau pengelola bidang.</p>
            </div>
            <a href="{{ route('public.articles.index') }}" class="inline-flex w-fit rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50">Lihat semua artikel</a>
        </div>

        @if ($latestArticles->isNotEmpty())
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($latestArticles as $article)
                    <x-public.article-card :article="$article" />
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <x-ui.empty-state title="Belum ada kajian yang dipublikasikan." description="Hasil kajian akan muncul setelah admin menerbitkan artikel pertama." />
            </div>
        @endif
    </section>

    <section id="kategori" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="rounded-[2rem] border border-emerald-100 bg-emerald-950 p-5 text-white shadow-xl shadow-emerald-900/10 sm:p-8">
            <div class="mb-5">
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-200">Kategori Artikel</p>
                <h2 class="mt-2 text-2xl font-extrabold">Pilih tema publikasi</h2>
            </div>
            <div class="flex gap-3 overflow-x-auto pb-1">
                @forelse ($categories as $category)
                    <a href="{{ route('public.categories.show', $category) }}" class="min-w-fit rounded-2xl border border-white/10 bg-white/10 px-4 py-3 transition hover:bg-white/15">
                        <span class="block text-sm font-extrabold">{{ $category->name }}</span>
                        <span class="mt-1 block text-xs text-emerald-100">{{ number_format($category->articles_count) }} artikel</span>
                    </a>
                @empty
                    <p class="text-sm text-emerald-100">Kategori artikel belum tersedia.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-5">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-700">Populer</p>
            <h2 class="mt-2 text-2xl font-extrabold text-slate-950 sm:text-3xl">Tulisan paling banyak dibaca</h2>
        </div>
        @if ($popularArticles->isNotEmpty())
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($popularArticles as $article)
                    <x-public.article-card :article="$article" compact />
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <x-ui.empty-state title="Belum ada tulisan populer." description="Statistik populer akan muncul setelah artikel mulai dibaca." />
            </div>
        @endif
    </section>

    <section id="tentang" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8 lg:grid-cols-[0.8fr_1.2fr]">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-700">Tentang</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-950">Pemuda Persis Cirengit</h2>
            </div>
            <p class="text-sm leading-7 text-slate-600 sm:text-base">
                Website ini menjadi ruang publik untuk menyimpan dan membagikan hasil kajian, sekaligus menjadi pintu masuk menuju sistem internal anggota. Phase ini menjaga kajian tetap mudah diakses tanpa mengganggu fitur administrasi, agenda, dan presensi yang sudah berjalan.
            </p>
        </div>
    </section>
@endsection
