@extends('layouts.public')

@section('title', 'Artikel - Pemuda Cirengit')
@section('meta_description', 'Daftar artikel dan hasil kajian Pemuda Persis Cirengit.')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-[2rem] border border-white/80 bg-white/85 p-5 shadow-sm backdrop-blur sm:p-8">
            <x-ui.breadcrumb :items="[
                ['label' => 'Beranda', 'url' => route('public.home')],
                ['label' => 'Artikel'],
            ]" />
            <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-700">Artikel</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-5xl">{{ $selectedCategory ? 'Kategori '.$selectedCategory->name : 'Semua Artikel' }}</h1>
                    <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Cari dan baca publikasi yang sudah diterbitkan dengan tampilan yang fokus pada kenyamanan membaca.</p>
                </div>
                <form method="GET" action="{{ route('public.articles.index') }}" class="flex flex-col gap-2 sm:flex-row">
                    @if ($selectedCategory)
                        <input type="hidden" name="category" value="{{ $selectedCategory->slug }}">
                    @endif
                    <input name="search" type="search" value="{{ $search }}" placeholder="Cari judul artikel" class="rounded-xl border-slate-200 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <select name="sort" class="rounded-xl border-slate-200 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Terbaru</option>
                        <option value="popular" @selected($sort === 'popular')>Populer</option>
                    </select>
                    <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Terapkan</button>
                </form>
            </div>
        </div>

        <div class="mt-5 flex gap-2 overflow-x-auto pb-1">
            <a href="{{ route('public.articles.index') }}" class="{{ ! $selectedCategory ? 'bg-emerald-700 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200' }} min-w-fit rounded-full px-4 py-2 text-sm font-bold">Semua</a>
            @foreach ($categories as $category)
                <a href="{{ route('public.categories.show', $category) }}" class="{{ $selectedCategory?->is($category) ? 'bg-emerald-700 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200' }} min-w-fit rounded-full px-4 py-2 text-sm font-bold">{{ $category->name }}</a>
            @endforeach
        </div>

        <div class="mt-6">
            @if ($articles->isNotEmpty())
                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($articles as $article)
                        <x-public.article-card :article="$article" />
                    @endforeach
                </div>
                <div class="mt-6">{{ $articles->links() }}</div>
            @else
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <x-ui.empty-state title="Belum ada artikel yang dipublikasikan." description="Coba ubah kata kunci atau filter kategori." />
                </div>
            @endif
        </div>
    </section>
@endsection
