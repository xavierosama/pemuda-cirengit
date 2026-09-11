@extends('layouts.admin')

@section('title', 'Edit Notifikasi - Pemuda Cirengit')
@section('section', 'Komunikasi')
@section('page-title', 'Edit Notifikasi')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Notifikasi', 'url' => route('admin-notifications.index')],
        ['label' => 'Edit'],
    ]" />
@endsection

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Komunikasi Anggota</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Edit Draft Notifikasi</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Perbarui draft sebelum dikirim ke anggota.</p>
                </div>
                <a href="{{ route('admin-notifications.show', $adminNotification) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Kembali</a>
            </div>
        </div>

        <form method="POST" action="{{ route('admin-notifications.update', $adminNotification) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            @method('PUT')
            @include('admin-notifications._form', ['adminNotification' => $adminNotification])
        </form>
    </div>
@endsection
