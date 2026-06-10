@extends('layouts.admin')

@section('title', 'Import Data Anggota - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Import Data Anggota')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-950">Import Data Anggota</h2>
                <p class="mt-1 text-sm text-slate-500">Gunakan template Excel agar format kolom data anggota seragam.</p>
            </div>

            <a href="{{ route('members.import.template') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                Download Template Excel
            </a>
        </div>

        @if ($errors->has('file'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ $errors->first('file') }}
            </div>
        @endif

        @if (session('import_result'))
            @php($result = session('import_result'))
            <section class="rounded-lg border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <h3 class="text-base font-bold text-emerald-950">Ringkasan Import</h3>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Data Ditambahkan</p>
                        <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($result['created']) }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Data Diupdate</p>
                        <p class="mt-2 text-2xl font-bold text-sky-700">{{ number_format($result['updated']) }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Data Gagal</p>
                        <p class="mt-2 text-2xl font-bold text-red-700">{{ number_format($result['failed']) }}</p>
                    </div>
                </div>

                @if (! empty($result['errors']))
                    <div class="mt-4 rounded-lg border border-red-200 bg-white p-4">
                        <p class="text-sm font-bold text-red-800">Error per baris</p>
                        <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-red-700">
                            @foreach ($result['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>
        @endif

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-bold text-slate-950">Upload File Excel</h3>
            <form method="POST" action="{{ route('members.import.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label for="file" class="block text-sm font-semibold text-slate-700">File Excel</label>
                    <input id="file" name="file" type="file" accept=".xlsx,.xls" required class="mt-2 block w-full rounded-lg border border-slate-300 text-sm text-slate-700 file:mr-4 file:border-0 file:bg-slate-900 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                    <p class="mt-2 text-xs text-slate-500">Format file: .xlsx atau .xls. Maksimal 5MB.</p>
                </div>

                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                    Import Data Anggota
                </button>
            </form>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-bold text-slate-950">Catatan Import</h3>
            <ul class="mt-4 list-disc space-y-2 pl-5 text-sm text-slate-600">
                <li>Gunakan template yang tersedia agar format kolom sesuai.</li>
                <li>Pastikan nama bidang dan jabatan sesuai dengan data master.</li>
                <li>Format tanggal: dd/mm/yyyy.</li>
                <li>Status anggota yang tersedia: active, inactive, alumni, moved.</li>
            </ul>
        </section>
    </div>
@endsection
