@extends('layouts.admin')

@section('title', 'Kategori Keuangan')
@section('page-title', 'Kategori Keuangan')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard Bendahara', 'url' => route('finance.dashboard')],
        ['label' => 'Kategori Keuangan'],
    ]" />
@endsection

@section('content')
    @php $filterCount = filled($type) + filled($status); @endphp
    <div class="space-y-4">
        <x-ui.card padding="md">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Master Keuangan</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Kategori Keuangan</h2>
                    <p class="mt-2 text-sm text-slate-500">Kelola kategori pemasukan dan pengeluaran.</p>
                </div>
                <a href="{{ route('finance.categories.create') }}" class="inline-flex justify-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Tambah Kategori</a>
            </div>
        </x-ui.card>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-4 border-b border-slate-200 px-5 py-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div>
                    <h3 class="font-bold text-slate-950">Daftar Kategori</h3>
                    <p class="mt-1 text-sm text-slate-500">Kategori nonaktif tidak muncul di form transaksi baru.</p>
                </div>
                <x-ui.table-toolbar
                    :action="route('finance.categories.index')"
                    search-placeholder="Cari kategori"
                    :search-value="$search"
                    :filter-count="$filterCount"
                    :reset-href="route('finance.categories.index')"
                    show-filter
                >
                    <x-slot:filters>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Tipe</label>
                                <select name="type" class="mt-2 w-full rounded-xl border-slate-300 text-sm">
                                    <option value="">Semua</option>
                                    <option value="income" @selected($type === 'income')>Pemasukan</option>
                                    <option value="expense" @selected($type === 'expense')>Pengeluaran</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Status</label>
                                <select name="status" class="mt-2 w-full rounded-xl border-slate-300 text-sm">
                                    <option value="">Semua</option>
                                    <option value="active" @selected($status === 'active')>Aktif</option>
                                    <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
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
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Tipe</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Transaksi</th>
                            <th class="sticky right-0 z-20 border-l border-slate-200 bg-slate-50 px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-4 py-4">
                                    <p class="font-bold text-slate-950">{{ $category->name }}</p>
                                    <p class="mt-1 line-clamp-1 text-sm text-slate-500">{{ $category->description ?: $category->slug }}</p>
                                </td>
                                <td class="px-4 py-4"><x-ui.status-badge :status="$category->type" :label="$category->typeLabel()" /></td>
                                <td class="px-4 py-4"><x-ui.status-badge :status="$category->is_active ? 'active' : 'inactive'" /></td>
                                <td class="px-4 py-4 text-sm font-semibold text-slate-700">{{ number_format($category->transactions_count) }}</td>
                                <td class="sticky right-0 border-l border-slate-100 bg-white px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <x-action-icon :href="route('finance.categories.edit', $category)" label="Edit" icon="pencil" />
                                        <x-action-icon :action="route('finance.categories.destroy', $category)" method="DELETE" label="Hapus" icon="trash" variant="red" confirm="Yakin ingin menghapus/nonaktifkan kategori ini?" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12"><x-ui.empty-state title="Belum ada kategori keuangan." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $categories->links() }}
    </div>
@endsection
