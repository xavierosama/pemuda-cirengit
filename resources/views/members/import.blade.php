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

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-bold text-slate-950">Catatan Import</h3>
            <ul class="mt-4 list-disc space-y-2 pl-5 text-sm text-slate-600">
                <li>Gunakan format tanggal dd/mm/yyyy.</li>
                <li>Pastikan nama bidang dan jabatan sesuai dengan data master.</li>
                <li>Status anggota yang tersedia: active, inactive, alumni, moved.</li>
            </ul>
        </section>

        <section class="rounded-lg border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
            Fitur upload import dapat menggunakan template ini. Tombol di halaman ini hanya menyiapkan file template Excel dan tidak mengubah data anggota.
        </section>
    </div>
@endsection
