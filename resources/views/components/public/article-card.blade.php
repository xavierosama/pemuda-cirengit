@props([
    'article',
    'compact' => false,
])

@php
    $date = $article->published_at ? \App\Support\DateFormatter::date($article->published_at) : '-';
    $author = $article->managedByBidang?->name ?? $article->author?->name ?? 'Pemuda Cirengit';
@endphp

<article {{ $attributes->merge(['class' => 'group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-xl hover:shadow-emerald-100/70']) }}>
    <a href="{{ route('public.articles.show', $article) }}" class="grid h-full sm:block">
        <div class="{{ $compact ? 'aspect-[16/9]' : 'aspect-[16/10]' }} overflow-hidden bg-gradient-to-br from-emerald-800 via-emerald-700 to-teal-900">
            @if ($article->banner_url)
                <img src="{{ $article->banner_url }}" alt="{{ $article->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            @else
                <div class="flex h-full w-full items-end bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.28),transparent_16rem)] p-5 text-white">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-100">Kajian Pemuda</p>
                        <p class="mt-2 line-clamp-2 text-xl font-extrabold leading-tight">{{ $article->title }}</p>
                    </div>
                </div>
            @endif
        </div>
        <div class="flex flex-1 flex-col p-4 sm:p-5">
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-500">
                @if ($article->category)
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700 ring-1 ring-inset ring-emerald-200">{{ $article->category->name }}</span>
                @endif
                <span>{{ $date }}</span>
                <span>&bull;</span>
                <span>{{ $article->reading_minutes }} menit baca</span>
            </div>
            <h3 class="mt-3 line-clamp-2 text-lg font-black leading-snug text-slate-950 group-hover:text-emerald-800 sm:text-xl">{{ $article->title }}</h3>
            @if ($article->excerpt)
                <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $article->excerpt }}</p>
            @endif
            <div class="mt-auto flex items-center justify-between gap-3 pt-4 text-sm">
                <span class="truncate font-semibold text-slate-500">{{ $author }} · {{ number_format($article->views_count) }} views</span>
                <span class="shrink-0 font-bold text-emerald-700">Baca Kajian</span>
            </div>
        </div>
    </a>
</article>
