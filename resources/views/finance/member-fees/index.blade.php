@extends('layouts.admin')

@section('title', 'Iuran Anggota')
@section('page-title', 'Iuran Anggota')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard Bendahara', 'url' => route('finance.dashboard')],
        ['label' => 'Iuran Anggota'],
    ]" />
@endsection

@section('content')
    @php
        $monthOptions = collect(range(1, 12))->mapWithKeys(fn ($item) => [$item => \Carbon\Carbon::create(null, $item, 1)->translatedFormat('F')]);
        $periodLabel = ($monthOptions[$month] ?? $month).' '.$year;
        $filterCount = filled($status) + filled($search);
        $compliance = $stats['total'] > 0 ? round(($stats['paid'] / $stats['total']) * 100) : 0;
        $statCards = [
            ['label' => 'Record Iuran', 'value' => number_format($stats['total']), 'tone' => 'slate'],
            ['label' => 'Lunas', 'value' => number_format($stats['paid']), 'tone' => 'emerald'],
            ['label' => 'Belum Bayar', 'value' => number_format($stats['unpaid']), 'tone' => 'amber'],
            ['label' => 'Dibebaskan', 'value' => number_format($stats['waived']), 'tone' => 'sky'],
            ['label' => 'Kepatuhan', 'value' => $compliance.'%', 'tone' => 'emerald'],
            ['label' => 'Nominal Default', 'value' => 'Rp '.number_format($stats['default_amount'], 0, ',', '.'), 'tone' => 'slate'],
        ];
    @endphp

    <div class="space-y-4">
        <x-ui.card padding="md">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Tracking-only</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Iuran Anggota - {{ $periodLabel }}</h2>
                    <p class="mt-2 text-sm text-slate-500">Bendahara mencatat status iuran. Member hanya melihat status, tanpa tombol bayar atau upload bukti.</p>
                </div>
                <form method="POST" action="{{ route('finance.member-fees.generate') }}" class="flex flex-col gap-2 rounded-2xl border border-emerald-100 bg-emerald-50 p-3 sm:flex-row sm:items-end">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Generate Periode</p>
                        <p class="text-sm font-semibold text-emerald-950">{{ $periodLabel }}</p>
                    </div>
                    <button type="submit" @disabled(! $hasFeeRecordsTable) class="inline-flex justify-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-600" onclick="return confirm('Generate iuran untuk semua anggota aktif pada periode ini? Record yang sudah ada akan dilewati.')">Generate Iuran</button>
                </form>
            </div>
        </x-ui.card>

        @if (session('success') || session('warning'))
            <div class="rounded-xl border {{ session('success') ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-800' }} px-4 py-3 text-sm font-semibold">{{ session('success') ?? session('warning') }}</div>
        @endif

        @unless ($hasFeeRecordsTable)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-bold">Tabel iuran belum tersedia.</p>
                <p class="mt-1 leading-6">Jalankan <code class="rounded bg-white/70 px-1.5 py-0.5 font-semibold">php artisan migrate</code> agar tabel <code class="rounded bg-white/70 px-1.5 py-0.5 font-semibold">member_fee_records</code> dibuat. Halaman ini sementara menampilkan angka 0.</p>
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            @foreach ($statCards as $card)
                <x-ui.card padding="sm">
                    <p class="line-clamp-1 text-xs font-bold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-2 text-xl font-bold {{ $card['tone'] === 'emerald' ? 'text-emerald-700' : ($card['tone'] === 'amber' ? 'text-amber-700' : ($card['tone'] === 'sky' ? 'text-sky-700' : 'text-slate-950')) }}">{{ $card['value'] }}</p>
                </x-ui.card>
            @endforeach
        </div>

        <x-ui.card padding="md">
            <form method="POST" action="{{ route('finance.member-fees.settings.update') }}" class="grid gap-4 lg:grid-cols-[1fr_180px_170px_170px_auto] lg:items-end">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-sm font-semibold text-slate-700" for="fee_name">Nama Setting</label>
                    <input id="fee_name" name="name" value="{{ old('name', 'Iuran Bulanan') }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm" placeholder="Iuran Bulanan">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700" for="default_amount">Nominal Default</label>
                    <input id="default_amount" name="default_amount" type="number" min="0" value="{{ old('default_amount', $stats['default_amount']) }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm" required>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700" for="effective_from">Berlaku Dari</label>
                    <input id="effective_from" name="effective_from" type="date" value="{{ old('effective_from', now()->startOfMonth()->toDateString()) }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700" for="effective_to">Berlaku Sampai</label>
                    <input id="effective_to" name="effective_to" type="date" value="{{ old('effective_to') }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm">
                </div>
                <button type="submit" class="inline-flex justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700 hover:bg-emerald-100">Simpan Nominal</button>
            </form>
            @if ($errors->any())
                <p class="mt-3 text-sm font-semibold text-red-600">{{ $errors->first() }}</p>
            @endif
        </x-ui.card>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-4 border-b border-slate-200 px-5 py-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div>
                    <h3 class="font-bold text-slate-950">Daftar Status Iuran</h3>
                    <p class="mt-1 text-sm text-slate-500">Cari anggota, filter status, lalu update status per orang.</p>
                </div>
                <x-ui.table-toolbar
                    :action="route('finance.member-fees.index')"
                    search-placeholder="Cari nama/NPA"
                    :search-value="$search"
                    :filter-count="$filterCount"
                    :reset-href="route('finance.member-fees.index')"
                    show-filter
                >
                    <x-slot:filters>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Tahun</label>
                                <input type="number" name="year" value="{{ $year }}" min="2020" max="2100" class="mt-2 w-full rounded-xl border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Bulan</label>
                                <select name="month" class="mt-2 w-full rounded-xl border-slate-300 text-sm">
                                    @foreach ($monthOptions as $value => $label)
                                        <option value="{{ $value }}" @selected((int) $month === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Status</label>
                                <select name="status" class="mt-2 w-full rounded-xl border-slate-300 text-sm">
                                    <option value="">Semua</option>
                                    @foreach (\App\Models\MemberFeeRecord::STATUSES as $value => $label)
                                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </x-slot:filters>
                    <x-per-page-selector :per-page="$perPage" :options="$perPageOptions" :query="$queryParams" />
                </x-ui.table-toolbar>
            </div>

            <div class="hidden overflow-x-auto xl:block">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Anggota</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Bulan</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Nominal</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Tanggal Bayar</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Catatan</th>
                            <th class="sticky right-0 z-20 border-l border-slate-200 bg-slate-50 px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($records as $record)
                            <tr>
                                <td class="px-4 py-4">
                                    <p class="font-bold text-slate-950">{{ $record->member?->full_name ?? '-' }}</p>
                                    <p class="mt-1 text-sm text-slate-500">NPA: {{ $record->member?->npa ?: '-' }}</p>
                                </td>
                                <td class="px-4 py-4 text-sm font-semibold text-slate-700">{{ $monthOptions[$record->month] ?? $record->month }} {{ $record->year }}</td>
                                <td class="px-4 py-4">
                                    <input form="fee-form-{{ $record->id }}" name="amount" type="number" min="0" value="{{ old('amount_'.$record->id, $record->amount) }}" class="w-32 rounded-xl border-slate-300 text-sm">
                                </td>
                                <td class="px-4 py-4">
                                    <select form="fee-form-{{ $record->id }}" name="status" class="w-36 rounded-xl border-slate-300 text-sm">
                                        @foreach (\App\Models\MemberFeeRecord::STATUSES as $value => $label)
                                            <option value="{{ $value }}" @selected($record->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-4">
                                    <input form="fee-form-{{ $record->id }}" name="paid_at" type="date" value="{{ optional($record->paid_at)->format('Y-m-d') }}" class="w-40 rounded-xl border-slate-300 text-sm">
                                </td>
                                <td class="px-4 py-4">
                                    <input form="fee-form-{{ $record->id }}" name="note" value="{{ $record->note }}" class="w-52 rounded-xl border-slate-300 text-sm" placeholder="Opsional">
                                </td>
                                <td class="sticky right-0 border-l border-slate-100 bg-white px-4 py-4 text-right">
                                    <form id="fee-form-{{ $record->id }}" method="POST" action="{{ route('finance.member-fees.update', $record) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex rounded-xl bg-emerald-700 px-3 py-2 text-sm font-bold text-white hover:bg-emerald-800">Simpan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-12"><x-ui.empty-state title="Belum ada data iuran untuk periode ini." description="{{ $hasFeeRecordsTable ? 'Klik Generate Iuran untuk membuat tagihan bulan ini bagi anggota aktif.' : 'Jalankan migration terlebih dahulu agar tabel member_fee_records tersedia.' }}" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-slate-100 xl:hidden">
                @forelse ($records as $record)
                    <article class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-bold text-slate-950">{{ $record->member?->full_name ?? '-' }}</h3>
                                <p class="mt-1 text-sm text-slate-500">NPA: {{ $record->member?->npa ?: '-' }} &middot; {{ $monthOptions[$record->month] ?? $record->month }} {{ $record->year }}</p>
                            </div>
                            <x-ui.status-badge :status="$record->displayStatus()" :label="$record->displayStatusLabel()" />
                        </div>
                        <form method="POST" action="{{ route('finance.member-fees.update', $record) }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Nominal</label>
                                <input name="amount" type="number" min="0" value="{{ $record->amount }}" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Status</label>
                                <select name="status" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                                    @foreach (\App\Models\MemberFeeRecord::STATUSES as $value => $label)
                                        <option value="{{ $value }}" @selected($record->status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Tanggal Bayar</label>
                                <input name="paid_at" type="date" value="{{ optional($record->paid_at)->format('Y-m-d') }}" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Catatan</label>
                                <input name="note" value="{{ $record->note }}" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                            </div>
                            <button type="submit" class="sm:col-span-2 inline-flex justify-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">Simpan Status</button>
                        </form>
                    </article>
                @empty
                    <div class="p-6"><x-ui.empty-state title="Belum ada data iuran untuk periode ini." description="{{ $hasFeeRecordsTable ? 'Klik Generate Iuran untuk membuat tagihan bulan ini bagi anggota aktif.' : 'Jalankan migration terlebih dahulu agar tabel member_fee_records tersedia.' }}" /></div>
                @endforelse
            </div>
        </div>

        {{ $records->links() }}
    </div>
@endsection
