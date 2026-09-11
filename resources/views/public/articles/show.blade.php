@extends('layouts.public')

@section('title', $article->title.' - Pemuda Cirengit')
@section('meta_description', $article->excerpt ?: str(strip_tags($article->content))->limit(150))
@section('og_type', 'article')
@section('og_image', $article->banner_url ?: '')

@section('content')
    @php
        $shareUrl = route('public.articles.show', $article);
        $shareText = $article->title.' - '.$shareUrl;
        $author = $article->managedByBidang?->name ?? $article->author?->name ?? 'Pemuda Cirengit';
        $hasDetailBanner = $article->banner_image && $article->banner_url;
    @endphp

    <article class="bg-white">
        <div class="mx-auto max-w-3xl px-4 pb-12 pt-8 sm:px-6 lg:px-8">
            <x-ui.breadcrumb :items="[
                ['label' => 'Beranda', 'url' => route('public.home')],
                ['label' => 'Artikel', 'url' => route('public.articles.index')],
                ['label' => str($article->title)->limit(42)],
            ]" />

            <header class="mt-6">
                <div class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                    @if ($article->category)
                        <a href="{{ route('public.categories.show', $article->category) }}" class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700 ring-1 ring-inset ring-emerald-200">{{ $article->category->name }}</a>
                    @endif
                    <span>Kajian Pemuda</span>
                </div>

                <h1 class="mt-5 text-3xl font-black leading-tight tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">{{ $article->title }}</h1>

                @if ($article->excerpt)
                    <p class="mt-5 text-lg leading-8 text-slate-600 sm:text-xl sm:leading-9">{{ $article->excerpt }}</p>
                @endif

                <div class="mt-6 flex flex-col gap-4 border-y border-slate-200 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-slate-500">
                        <span class="font-bold text-slate-800">{{ $author }}</span>
                        <span>&middot;</span>
                        <span>{{ \App\Support\DateFormatter::date($article->published_at) }}</span>
                        <span>&middot;</span>
                        <span>{{ $article->reading_minutes }} menit baca</span>
                        <span>&middot;</span>
                        <span>{{ number_format($article->views_count) }} views</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2" x-data="{ copied: false }">
                        <a href="https://wa.me/?text={{ urlencode($shareText) }}" target="_blank" rel="noopener" class="inline-flex h-9 items-center rounded-full border border-slate-200 px-3 text-xs font-bold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">WhatsApp</a>
                        <a href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($article->title) }}" target="_blank" rel="noopener" class="inline-flex h-9 items-center rounded-full border border-slate-200 px-3 text-xs font-bold text-slate-600 transition hover:border-sky-200 hover:bg-sky-50 hover:text-sky-700">Telegram</a>
                        <button type="button" class="inline-flex h-9 items-center rounded-full border border-slate-200 px-3 text-xs font-bold text-slate-600 transition hover:bg-slate-50" @click="navigator.clipboard.writeText(@js($shareUrl)).then(() => { copied = true; setTimeout(() => copied = false, 1800) })">
                            <span x-show="! copied">Copy</span>
                            <span x-cloak x-show="copied">Disalin</span>
                        </button>
                    </div>
                </div>
            </header>

            @if ($hasDetailBanner)
                <div class="mt-8 overflow-hidden rounded-3xl shadow-xl shadow-emerald-950/10">
                    <img src="{{ $article->banner_url }}" alt="{{ $article->title }}" class="aspect-[16/8] max-h-[34rem] w-full object-cover">
                </div>
            @endif

            <div class="{{ $hasDetailBanner ? 'mt-10' : 'mt-8' }}">
                <div class="article-content">
                    {!! \App\Support\Articles\ArticleContent::toHtml($article->content) !!}
                </div>

                @if ($article->youtube_embed_url)
                    <section class="mt-10">
                        <h2 class="text-xl font-black text-slate-950">Video Kajian</h2>
                        <div class="mt-4 aspect-video overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 shadow-sm">
                            <iframe
                                src="{{ $article->youtube_embed_url }}"
                                title="Video {{ $article->title }}"
                                class="h-full w-full"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                    </section>
                @endif

                <footer class="mt-12 border-t border-slate-200 pt-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Bagikan Kajian</p>
                            @if ($article->category)
                                <a href="{{ route('public.categories.show', $article->category) }}" class="mt-2 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-sm font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">{{ $article->category->name }}</a>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2" x-data="{ copied: false }">
                            <a href="https://wa.me/?text={{ urlencode($shareText) }}" target="_blank" rel="noopener" class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700 hover:bg-emerald-100">WhatsApp</a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="rounded-full bg-blue-50 px-4 py-2 text-sm font-bold text-blue-700 hover:bg-blue-100">Facebook</a>
                            <button type="button" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50" @click="navigator.clipboard.writeText(@js($shareUrl)).then(() => { copied = true; setTimeout(() => copied = false, 1800) })">
                                <span x-show="! copied">Copy Link</span>
                                <span x-cloak x-show="copied">Link disalin</span>
                            </button>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        @if ($relatedArticles->isNotEmpty())
            <section class="border-t border-slate-200 bg-slate-50 py-10">
                <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">Baca juga</p>
                            <h2 class="mt-2 text-2xl font-black text-slate-950">Artikel terkait</h2>
                        </div>
                    </div>
                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        @foreach ($relatedArticles as $related)
                            <x-public.article-card :article="$related" compact />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </article>
@endsection
