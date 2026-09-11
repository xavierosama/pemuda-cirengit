@extends('layouts.admin')

@section('title', 'Tambah Transaksi Keuangan')
@section('page-title', 'Tambah Transaksi')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard Bendahara', 'url' => route('finance.dashboard')],
        ['label' => 'Transaksi', 'url' => route('finance.transactions.index')],
        ['label' => 'Tambah'],
    ]" />
@endsection

@section('content')
    <div class="space-y-4">
        <x-ui.card padding="md">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Keuangan</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-950">Tambah Transaksi</h2>
            <p class="mt-2 text-sm text-slate-500">Catat pemasukan atau pengeluaran kas organisasi. Nominal selalu disimpan positif.</p>
        </x-ui.card>

        <form method="POST" action="{{ route('finance.transactions.store') }}">
            @include('finance.transactions._form')
        </form>
    </div>
@endsection
