@extends('layouts.admin')

@section('title', 'Tambah Notifikasi - Pemuda Cirengit')
@section('section', 'Komunikasi')
@section('page-title', 'Tambah Notifikasi')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Notifikasi', 'url' => route('admin-notifications.index')],
        ['label' => 'Tambah'],
    ]" />
@endsection

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Komunikasi Anggota</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Tambah Notifikasi</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Tulis pesan, pilih target, lalu simpan sebagai draft atau kirim sekarang.</p>
                </div>
                <a href="{{ route('admin-notifications.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Kembali</a>
            </div>
        </div>

        <form method="POST" action="{{ route('admin-notifications.store') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @include('admin-notifications._form', ['adminNotification' => null])
        </form>
    </div>
@endsection
