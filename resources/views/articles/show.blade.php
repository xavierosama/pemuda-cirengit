@extends('layouts.admin')

@section('title', 'Detail Artikel Kajian - Pemuda Cirengit')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Artikel Kajian', 'url' => route('articles.index')],
        ['label' => 'Detail Artikel'],
    ]" />
@endsection

@section('content')
    <div class="max-w-6xl space-y-4 sm:space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <x-ui.page-header title="{{ $article->title }}" eyebrow="Artikel Kajian" description="{{ $article->excerpt ?: 'Detail artikel dan status publikasi.' }}">
            <x-slot:action>
                <x-ui.button :href="route('articles.edit', $article)" variant="secondary">Edit Artikel</x-ui.button>
                @if ($article->isPublished())
                    <x-ui.button :href="route('public.articles.show', $article)">Lihat Publik</x-ui.button>
                @endif
            </x-slot:action>
        </x-ui.page-header>

        <div class="grid gap-4 sm:gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
            <x-ui.card padding="md">
                @if ($article->banner_url)
                    <img src="{{ $article->banner_url }}" alt="{{ $article->title }}" class="mb-5 aspect-video w-full rounded-2xl object-cover">
                @else
                    <div class="mb-5 aspect-video rounded-2xl bg-gradient-to-br from-emerald-800 via-emerald-700 to-teal-900 p-6 text-white">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-100">Default Banner</p>
                        <p class="mt-3 max-w-xl text-2xl font-extrabold">{{ $article->title }}</p>
                    </div>
                @endif

                <div class="article-content article-content-compact">
                    {!! \App\Support\Articles\ArticleContent::toHtml($article->content) !!}
                </div>
            </x-ui.card>

            <x-ui.card padding="md">
                <h3 class="text-base font-bold text-slate-950">Metadata</h3>
                <dl class="mt-4 space-y-4">
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Status</dt><dd class="mt-1"><x-ui.status-badge :status="$article->status" :label="\App\Models\Article::STATUSES[$article->status] ?? $article->status" /></dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Kategori</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $article->category?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Bidang</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $article->managedByBidang?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Author</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $article->author?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Published At</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $article->published_at ? \App\Support\DateFormatter::dateTime($article->published_at) : '-' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Views</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ number_format($article->views_count) }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">YouTube</dt><dd class="mt-1 break-all text-sm font-semibold text-slate-900">{{ $article->youtube_url ?: '-' }}</dd></div>
                </dl>
            </x-ui.card>
        </div>
    </div>
@endsection
