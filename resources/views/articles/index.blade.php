@extends('layouts.admin')

@section('title', 'Artikel Kajian - Pemuda Cirengit')
@section('section', 'Publikasi')
@section('page-title', 'Artikel Kajian')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Artikel Kajian'],
    ]" />
@endsection

@section('content')
    @php
        $summaryCards = [
            ['label' => 'Total Artikel', 'value' => $articleStats['total'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
            ['label' => 'Published', 'value' => $articleStats['published'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Draft', 'value' => $articleStats['draft'], 'class' => 'bg-amber-50 text-amber-700 ring-amber-100'],
            ['label' => 'Archived', 'value' => $articleStats['archived'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
        ];
        $filterCount = collect([$status, $categoryId])->filter(fn ($value) => filled($value))->count();
    @endphp

    <div class="space-y-4 sm:space-y-6">
        <x-ui.page-header
            title="Artikel Kajian"
            eyebrow="Publikasi"
            description="Kelola hasil kajian keilmuan yang tampil pada landing page publik."
        >
            <x-slot:action>
                <x-ui.button :href="route('articles.create')">Tambah Artikel</x-ui.button>
                <x-ui.button :href="route('public.articles.index')" variant="secondary">Lihat Publik</x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            @foreach ($summaryCards as $card)
                <x-ui.card padding="sm">
                    <div class="{{ $card['class'] }} inline-flex rounded-full px-2 py-1 text-[11px] font-semibold ring-1 ring-inset sm:px-2.5 sm:text-xs">{{ $card['label'] }}</div>
                    <p class="mt-3 text-2xl font-bold text-slate-950 sm:mt-4 sm:text-3xl">{{ number_format($card['value']) }}</p>
                </x-ui.card>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-4 border-b border-slate-200 px-5 py-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div>
                    <h3 class="text-base font-bold text-slate-950">Tabel Artikel Kajian</h3>
                    <p class="mt-1 text-sm text-slate-500">Artikel published tampil di landing page dan daftar kajian publik.</p>
                </div>
                <x-ui.table-toolbar
                    :action="route('articles.index')"
                    search-placeholder="Cari judul atau excerpt"
                    :search-value="$search"
                    :search-hidden="[
                        'sort' => $currentSort,
                        'direction' => $currentDirection,
                        'per_page' => $perPage,
                        'status' => $status,
                        'article_category_id' => $categoryId,
                    ]"
                    :filter-hidden="[
                        'sort' => $currentSort,
                        'direction' => $currentDirection,
                        'per_page' => $perPage,
                    ]"
                    :filter-count="$filterCount"
                    :reset-href="route('articles.index')"
                    show-filter
                >
                    <x-slot:filters>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="status_filter" class="text-sm font-semibold text-slate-700">Status</label>
                                <select id="status_filter" name="status" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                    <option value="">Semua status</option>
                                    @foreach (\App\Models\Article::STATUSES as $value => $label)
                                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="category_filter" class="text-sm font-semibold text-slate-700">Kategori</label>
                                <select id="category_filter" name="article_category_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                    <option value="">Semua kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </x-slot:filters>

                    <x-per-page-selector :per-page="$perPage" :options="$perPageOptions" :query="$queryParams" />
                </x-ui.table-toolbar>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">No</th>
                            <x-sortable-th field="title" label="Artikel" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Kategori</th>
                            <x-sortable-th field="status" label="Status" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="views_count" label="Views" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="published_at" label="Published" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="sticky right-0 z-20 whitespace-nowrap border-l border-slate-200 bg-slate-50 px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500 shadow-[-8px_0_12px_-12px_rgba(15,23,42,0.35)]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($articles as $article)
                            <tr class="align-top transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">{{ $articles->firstItem() + $loop->index }}</td>
                                <td class="max-w-md px-3 py-4">
                                    <p class="line-clamp-2 text-sm font-semibold text-slate-900">{{ $article->title }}</p>
                                    <p class="mt-1 line-clamp-1 text-xs text-slate-500">{{ $article->author?->name ?? '-' }}{{ $article->managedByBidang?->name ? ' - '.$article->managedByBidang->name : '' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ $article->category?->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-4"><x-ui.status-badge :status="$article->status" :label="\App\Models\Article::STATUSES[$article->status] ?? $article->status" /></td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-slate-700">{{ number_format($article->views_count) }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ $article->published_at ? \App\Support\DateFormatter::date($article->published_at) : '-' }}</td>
                                <td class="sticky right-0 z-10 whitespace-nowrap border-l border-slate-100 bg-white px-3 py-4 text-right text-sm font-semibold shadow-[-8px_0_12px_-12px_rgba(15,23,42,0.35)]">
                                    <div class="flex justify-end gap-1.5">
                                        <x-action-icon :href="route('articles.show', $article)" label="Detail" icon="eye" variant="blue" />
                                        <x-ui.action-dropdown>
                                            @if ($article->isPublished())
                                                <x-ui.action-dropdown-item :href="route('public.articles.show', $article)" label="Lihat Publik" icon="eye" />
                                            @endif
                                            <x-ui.action-dropdown-item :href="route('articles.edit', $article)" label="Edit" icon="pencil" />
                                            <x-ui.action-dropdown-item
                                                :action="route('articles.destroy', $article)"
                                                method="DELETE"
                                                label="Hapus"
                                                icon="trash"
                                                variant="danger"
                                                confirm="Yakin ingin menghapus artikel ini?"
                                                confirm-title="Hapus Artikel?"
                                                confirm-description="Artikel akan masuk soft delete dan tidak tampil di publik."
                                                confirm-text="Hapus"
                                                confirm-variant="danger"
                                            />
                                        </x-ui.action-dropdown>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12">
                                    <x-ui.empty-state title="Belum ada artikel kajian." description="Buat draft artikel pertama untuk mulai mengisi landing page publik." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $articles->links() }}
    </div>
@endsection
