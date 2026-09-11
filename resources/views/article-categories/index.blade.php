@extends('layouts.admin')

@section('title', 'Kategori Kajian - Pemuda Cirengit')
@section('section', 'Publikasi')
@section('page-title', 'Kategori Kajian')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Kategori Kajian'],
    ]" />
@endsection

@section('content')
    @php
        $summaryCards = [
            ['label' => 'Total Kategori', 'value' => $categoryStats['total'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
            ['label' => 'Kategori Aktif', 'value' => $categoryStats['active'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Kategori Nonaktif', 'value' => $categoryStats['inactive'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
        ];
        $filterCount = filled($status) ? 1 : 0;
    @endphp

    <div class="space-y-4 sm:space-y-6">
        <x-ui.page-header
            title="Kategori Kajian"
            eyebrow="Publikasi"
            description="Kelola kategori untuk mengelompokkan hasil kajian keilmuan."
        >
            <x-slot:action>
                <x-ui.button :href="route('article-categories.create')">Tambah Kategori</x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        @if (session('success') || session('warning'))
            <div class="{{ session('warning') ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }} rounded-lg border px-4 py-3 text-sm font-medium">{{ session('success') ?? session('warning') }}</div>
        @endif

        <div class="grid grid-cols-2 gap-3 sm:gap-4 sm:grid-cols-3">
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
                    <h3 class="text-base font-bold text-slate-950">Tabel Kategori Kajian</h3>
                    <p class="mt-1 text-sm text-slate-500">Kategori aktif tampil pada landing page dan filter publik.</p>
                </div>
                <x-ui.table-toolbar
                    :action="route('article-categories.index')"
                    search-placeholder="Cari kategori"
                    :search-value="$search"
                    :search-hidden="[
                        'sort' => $currentSort,
                        'direction' => $currentDirection,
                        'per_page' => $perPage,
                        'status' => $status,
                    ]"
                    :filter-hidden="[
                        'sort' => $currentSort,
                        'direction' => $currentDirection,
                        'per_page' => $perPage,
                    ]"
                    :filter-count="$filterCount"
                    :reset-href="route('article-categories.index')"
                    show-filter
                >
                    <x-slot:filters>
                        <div>
                            <label for="status_filter" class="text-sm font-semibold text-slate-700">Status</label>
                            <select id="status_filter" name="status" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                <option value="">Semua status</option>
                                <option value="active" @selected($status === 'active')>Aktif</option>
                                <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                            </select>
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
                            <x-sortable-th field="name" label="Kategori" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="sort_order" label="Urutan" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="is_active" label="Status" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Artikel</th>
                            <x-sortable-th field="created_at" label="Dibuat" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="sticky right-0 z-20 whitespace-nowrap border-l border-slate-200 bg-slate-50 px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500 shadow-[-8px_0_12px_-12px_rgba(15,23,42,0.35)]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($categories as $category)
                            <tr class="align-top transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">{{ $categories->firstItem() + $loop->index }}</td>
                                <td class="max-w-xs px-3 py-4">
                                    <p class="line-clamp-2 text-sm font-semibold text-slate-900">{{ $category->name }}</p>
                                    <p class="mt-1 line-clamp-1 text-xs text-slate-500">{{ $category->slug }}</p>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ $category->sort_order ?? '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    <x-ui.status-badge :status="$category->is_active ? 'active' : 'inactive'" :label="$category->is_active ? 'Aktif' : 'Nonaktif'" />
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-slate-700">{{ number_format($category->articles_count) }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ \App\Support\DateFormatter::date($category->created_at) }}</td>
                                <td class="sticky right-0 z-10 whitespace-nowrap border-l border-slate-100 bg-white px-3 py-4 text-right text-sm font-semibold shadow-[-8px_0_12px_-12px_rgba(15,23,42,0.35)]">
                                    <div class="flex justify-end gap-1.5">
                                        <x-action-icon :href="route('article-categories.show', $category)" label="Detail" icon="eye" variant="blue" />
                                        <x-ui.action-dropdown>
                                            <x-ui.action-dropdown-item :href="route('article-categories.edit', $category)" label="Edit" icon="pencil" />
                                            <x-ui.action-dropdown-item
                                                :action="route('article-categories.destroy', $category)"
                                                method="DELETE"
                                                label="Hapus"
                                                icon="trash"
                                                variant="danger"
                                                confirm="Yakin ingin menghapus kategori ini?"
                                                confirm-title="Hapus Kategori?"
                                                confirm-description="Kategori hanya dapat dihapus jika belum digunakan artikel."
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
                                    <x-ui.empty-state title="Belum ada kategori kajian." description="Tambahkan kategori seperti Aqidah, Fiqih, Akhlak, atau Kajian Umum." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $categories->links() }}
    </div>
@endsection
