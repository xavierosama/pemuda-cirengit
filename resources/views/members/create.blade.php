@extends('layouts.admin')

@section('title', 'Tambah Data Anggota - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Tambah Data Anggota')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Data Anggota', 'url' => route('members.index')],
        ['label' => 'Tambah Anggota'],
    ]" />
@endsection

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Master Data</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Tambah Anggota</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Lengkapi data anggota, NPA, kontak, bidang, jabatan, dan catatan administrasi.</p>
                </div>
                <a href="{{ route('members.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Kembali</a>
            </div>
        </div>

        <form method="POST" action="{{ route('members.store') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @include('members._form')
        </form>
    </div>
@endsection
