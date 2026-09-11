@extends('layouts.admin')

@section('title', 'Transaksi Keuangan')
@section('page-title', $type === 'income' ? 'Pemasukan' : ($type === 'expense' ? 'Pengeluaran' : 'Transaksi Keuangan'))
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard Bendahara', 'url' => route('finance.dashboard')],
        ['label' => $type === 'income' ? 'Pemasukan' : ($type === 'expense' ? 'Pengeluaran' : 'Transaksi')],
    ]" />
@endsection

@section('content')
    @php
        $filterCount = filled($type) + filled($categoryId) + filled($month);
        $title = $type === 'income' ? 'Pemasukan' : ($type === 'expense' ? 'Pengeluaran' : 'Semua Transaksi');
        $createType = in_array($type, ['income', 'expense'], true) ? $type : 'income';
        $monthOptions = collect(range(1, 12))->mapWithKeys(fn ($item) => [$item => \Carbon\Carbon::create(null, $item, 1)->translatedFormat('F')]);
    @endphp

    <div class="space-y-4">
        <x-ui.card padding="md">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Kas Organisasi</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">{{ $title }}</h2>
                    <p class="mt-2 text-sm text-slate-500">Kelola catatan pemasukan dan pengeluaran tanpa nominal negatif.</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <a href="{{ route('finance.transactions.create', ['type' => 'income']) }}" class="inline-flex justify-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Tambah Pemasukan</a>
                    <a href="{{ route('finance.transactions.create', ['type' => 'expense']) }}" class="inline-flex justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-100">Tambah Pengeluaran</a>
                </div>
            </div>
        </x-ui.card>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-4 border-b border-slate-200 px-5 py-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div>
                    <h3 class="font-bold text-slate-950">Daftar Transaksi</h3>
                    <p class="mt-1 text-sm text-slate-500">Filter periode, tipe, dan kategori untuk menyiapkan laporan cepat.</p>
                </div>
                <x-ui.table-toolbar
                    :action="route('finance.transactions.index')"
                    search-placeholder="Cari judul/catatan/ref"
                    :search-value="$search"
                    :filter-count="$filterCount"
                    :reset-href="route('finance.transactions.index')"
                    show-filter
                >
                    <x-slot:filters>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Tipe</label>
                                <select name="type" class="mt-2 w-full rounded-xl border-slate-300 text-sm">
                                    <option value="">Semua</option>
                                    <option value="income" @selected($type === 'income')>Pemasukan</option>
                                    <option value="expense" @selected($type === 'expense')>Pengeluaran</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Kategori</label>
                                <select name="category" class="mt-2 w-full rounded-xl border-slate-300 text-sm">
                                    <option value="">Semua</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((int) $categoryId === $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Tahun</label>
                                <input type="number" name="year" value="{{ $year }}" min="2020" max="2100" class="mt-2 w-full rounded-xl border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Bulan</label>
                                <select name="month" class="mt-2 w-full rounded-xl border-slate-300 text-sm">
                                    <option value="">Semua bulan</option>
                                    @foreach ($monthOptions as $value => $label)
                                        <option value="{{ $value }}" @selected((int) $month === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </x-slot:filters>
                    <x-per-page-selector :per-page="$perPage" :options="$perPageOptions" :query="$queryParams" />
                </x-ui.table-toolbar>
            </div>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Transaksi</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Tipe</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Nominal</th>
                            <th class="sticky right-0 z-20 border-l border-slate-200 bg-slate-50 px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td class="px-4 py-4 text-sm font-semibold text-slate-700">{{ \App\Support\DateFormatter::date($transaction->transaction_date) }}</td>
                                <td class="px-4 py-4">
                                    <p class="font-bold text-slate-950">{{ $transaction->title }}</p>
                                    <p class="mt-1 line-clamp-1 text-sm text-slate-500">{{ $transaction->description ?: ($transaction->reference_no ?: 'Tanpa catatan') }}</p>
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-700">{{ $transaction->category?->name ?? '-' }}</td>
                                <td class="px-4 py-4"><x-ui.status-badge :status="$transaction->type" :label="$transaction->typeLabel()" /></td>
                                <td class="px-4 py-4 text-right text-sm font-bold {{ $transaction->type === 'income' ? 'text-emerald-700' : 'text-red-700' }}">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td class="sticky right-0 border-l border-slate-100 bg-white px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <x-action-icon :href="route('finance.transactions.edit', $transaction)" label="Edit" icon="pencil" />
                                        <x-action-icon :action="route('finance.transactions.destroy', $transaction)" method="DELETE" label="Hapus" icon="trash" variant="red" confirm="Yakin ingin menghapus transaksi ini?" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12"><x-ui.empty-state title="Belum ada transaksi." description="Tambahkan pemasukan atau pengeluaran untuk mulai membaca kondisi kas." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-slate-100 lg:hidden">
                @forelse ($transactions as $transaction)
                    <article class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-500">{{ \App\Support\DateFormatter::date($transaction->transaction_date) }}</p>
                                <h3 class="mt-1 font-bold text-slate-950">{{ $transaction->title }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $transaction->category?->name ?? '-' }}</p>
                            </div>
                            <x-ui.status-badge :status="$transaction->type" :label="$transaction->typeLabel()" />
                        </div>
                        <p class="mt-3 text-lg font-bold {{ $transaction->type === 'income' ? 'text-emerald-700' : 'text-red-700' }}">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                        @if ($transaction->description || $transaction->reference_no)
                            <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $transaction->description ?: $transaction->reference_no }}</p>
                        @endif
                        <div class="mt-4 flex justify-end gap-2">
                            <x-action-icon :href="route('finance.transactions.edit', $transaction)" label="Edit" icon="pencil" />
                            <x-action-icon :action="route('finance.transactions.destroy', $transaction)" method="DELETE" label="Hapus" icon="trash" variant="red" confirm="Yakin ingin menghapus transaksi ini?" />
                        </div>
                    </article>
                @empty
                    <div class="p-6"><x-ui.empty-state title="Belum ada transaksi." /></div>
                @endforelse
            </div>
        </div>

        {{ $transactions->links() }}
    </div>
@endsection
