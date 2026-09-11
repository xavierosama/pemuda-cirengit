@extends('layouts.admin')

@section('title', 'Dashboard Bendahara - Pemuda Cirengit')
@section('section', 'Keuangan')
@section('page-title', 'Dashboard Bendahara')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard Bendahara', 'url' => route('finance.dashboard')],
        ['label' => 'Dashboard Bendahara'],
    ]" />
@endsection

@section('content')
    @php
        $money = fn ($value) => 'Rp '.number_format((int) $value, 0, ',', '.');
        $months = collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => now()->setMonth($m)->translatedFormat('F')]);
        $cards = [
            ['label' => 'Saldo Saat Ini', 'value' => $money($summary['balance']), 'tone' => 'emerald'],
            ['label' => 'Pemasukan Bulan Ini', 'value' => $money($summary['month_income']), 'tone' => 'blue'],
            ['label' => 'Pengeluaran Bulan Ini', 'value' => $money($summary['month_expense']), 'tone' => 'red'],
            ['label' => 'Surplus/Defisit', 'value' => $money($summary['month_surplus']), 'tone' => $summary['month_surplus'] >= 0 ? 'emerald' : 'red'],
            ['label' => 'Iuran Masuk', 'value' => $money($summary['fee_income']), 'tone' => 'violet'],
            ['label' => 'Sudah Bayar', 'value' => number_format($summary['paid_count']).' anggota', 'tone' => 'emerald'],
            ['label' => 'Belum Bayar', 'value' => number_format($summary['unpaid_count']).' anggota', 'tone' => 'amber'],
            ['label' => 'Kepatuhan Iuran', 'value' => $summary['compliance'].'%', 'tone' => 'blue'],
        ];
        $toneClasses = [
            'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            'blue' => 'bg-sky-50 text-sky-700 ring-sky-100',
            'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
            'red' => 'bg-red-50 text-red-700 ring-red-100',
            'violet' => 'bg-violet-50 text-violet-700 ring-violet-100',
            'slate' => 'bg-slate-50 text-slate-700 ring-slate-200',
        ];
    @endphp

    <div class="space-y-4 sm:space-y-6">
        <x-ui.card padding="md">
            <form method="GET" action="{{ route('finance.dashboard') }}" class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Command Center Keuangan</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Dashboard Bendahara</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Pantau saldo, transaksi, dan kepatuhan iuran anggota dalam satu halaman.</p>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:flex">
                    <select name="month" class="rounded-xl border-slate-200 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        @foreach ($months as $number => $name)
                            <option value="{{ $number }}" @selected($month === $number)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <input name="year" type="number" value="{{ $year }}" min="2020" max="2100" class="rounded-xl border-slate-200 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <button class="col-span-2 rounded-xl bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800 sm:col-span-1">Terapkan</button>
                </div>
            </form>
        </x-ui.card>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ($cards as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <span class="{{ $toneClasses[$card['tone']] }} inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 ring-inset">{{ $card['label'] }}</span>
                    <p class="mt-3 break-words text-xl font-black text-slate-950 sm:text-2xl">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            @foreach ($insights as $insight)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <span class="{{ $toneClasses[$insight['tone']] ?? $toneClasses['slate'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset">{{ $insight['title'] }}</span>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $insight['message'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <x-ui.card padding="md">
                <h3 class="font-bold text-slate-950">Tren Pemasukan vs Pengeluaran</h3>
                <div class="mt-4 h-72"><canvas id="financeTrendChart"></canvas></div>
            </x-ui.card>
            <x-ui.card padding="md">
                <h3 class="font-bold text-slate-950">Tren Pembayaran Iuran</h3>
                <div class="mt-4 h-72"><canvas id="feeTrendChart"></canvas></div>
            </x-ui.card>
            <x-ui.card padding="md">
                <h3 class="font-bold text-slate-950">Komposisi Pemasukan</h3>
                <div class="mt-4 h-72"><canvas id="incomeCategoryChart"></canvas></div>
            </x-ui.card>
            <x-ui.card padding="md">
                <h3 class="font-bold text-slate-950">Komposisi Pengeluaran</h3>
                <div class="mt-4 h-72"><canvas id="expenseCategoryChart"></canvas></div>
            </x-ui.card>
        </div>

        <div class="grid gap-4 lg:grid-cols-[0.85fr_1.15fr]">
            <x-ui.card padding="md">
                <h3 class="font-bold text-slate-950">Status Iuran Bulan Ini</h3>
                <div class="mt-4 h-64"><canvas id="feeStatusChart"></canvas></div>
            </x-ui.card>
            <x-ui.card padding="md">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-slate-950">Transaksi Terbaru</h3>
                        <p class="mt-1 text-sm text-slate-500">5 transaksi terakhir yang dicatat.</p>
                    </div>
                    <a href="{{ route('finance.transactions.create') }}" class="rounded-xl bg-emerald-700 px-3 py-2 text-xs font-bold text-white">Tambah</a>
                </div>
                <div class="mt-4 divide-y divide-slate-100">
                    @forelse ($latestTransactions as $transaction)
                        <div class="flex items-center justify-between gap-3 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-900">{{ $transaction->title }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $transaction->category?->name ?? '-' }} &middot; {{ \App\Support\DateFormatter::date($transaction->transaction_date) }}</p>
                            </div>
                            <div class="text-right">
                                <x-ui.status-badge :status="$transaction->type" :label="$transaction->typeLabel()" />
                                <p class="mt-1 text-sm font-black text-slate-950">{{ $money($transaction->amount) }}</p>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state title="Belum ada transaksi." description="Transaksi terbaru akan muncul setelah bendahara mencatat pemasukan atau pengeluaran." />
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const financeCharts = @json($charts);
        const colors = ['#059669', '#0284c7', '#f59e0b', '#ef4444', '#7c3aed', '#64748b'];

        new Chart(document.getElementById('financeTrendChart'), {
            type: 'bar',
            data: { labels: financeCharts.monthlyTrend.labels, datasets: [
                { label: 'Pemasukan', data: financeCharts.monthlyTrend.income, backgroundColor: '#059669' },
                { label: 'Pengeluaran', data: financeCharts.monthlyTrend.expense, backgroundColor: '#ef4444' },
            ] },
            options: { responsive: true, maintainAspectRatio: false }
        });
        new Chart(document.getElementById('feeTrendChart'), {
            type: 'line',
            data: { labels: financeCharts.feeTrend.labels, datasets: [
                { label: 'Lunas', data: financeCharts.feeTrend.paid, borderColor: '#059669', backgroundColor: '#05966922', tension: 0.35 },
                { label: 'Total Record', data: financeCharts.feeTrend.total, borderColor: '#0284c7', backgroundColor: '#0284c722', tension: 0.35 },
            ] },
            options: { responsive: true, maintainAspectRatio: false }
        });
        new Chart(document.getElementById('incomeCategoryChart'), {
            type: 'doughnut',
            data: { labels: financeCharts.incomeCategories.labels, datasets: [{ data: financeCharts.incomeCategories.data, backgroundColor: colors }] },
            options: { responsive: true, maintainAspectRatio: false }
        });
        new Chart(document.getElementById('expenseCategoryChart'), {
            type: 'doughnut',
            data: { labels: financeCharts.expenseCategories.labels, datasets: [{ data: financeCharts.expenseCategories.data, backgroundColor: colors }] },
            options: { responsive: true, maintainAspectRatio: false }
        });
        new Chart(document.getElementById('feeStatusChart'), {
            type: 'doughnut',
            data: { labels: financeCharts.feeStatus.labels, datasets: [{ data: financeCharts.feeStatus.data, backgroundColor: ['#059669', '#f59e0b', '#64748b'] }] },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
@endpush
