@extends('layouts.admin')

@section('title', 'Dashboard - Pemuda Cirengit')
@section('section', 'Ringkasan')
@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Pemuda Persis Cirengit</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-950">Selamat datang di sistem administrasi internal.</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Dashboard ini disiapkan sebagai pusat ringkasan untuk data anggota, agenda, kegiatan aktual, dan presensi berbasis login serta lokasi.
                </p>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Data Anggota</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">0</p>
                <p class="mt-2 text-xs font-medium text-slate-500">Belum ada data anggota.</p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Jadwal Agenda</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">0</p>
                <p class="mt-2 text-xs font-medium text-slate-500">Agenda akan tampil setelah modul dibuat.</p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Kegiatan Aktual</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">0</p>
                <p class="mt-2 text-xs font-medium text-slate-500">Rekap kegiatan belum tersedia.</p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Daftar Hadir</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">0</p>
                <p class="mt-2 text-xs font-medium text-slate-500">Presensi akan dihitung dari kegiatan aktif.</p>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-950">Agenda Terdekat</h3>
                        <p class="mt-1 text-sm text-slate-500">Ringkasan jadwal akan muncul di area ini.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">MVP</span>
                </div>

                <div class="mt-6 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                    <p class="text-sm font-semibold text-slate-700">Belum ada agenda.</p>
                    <p class="mt-1 text-sm text-slate-500">Modul jadwal agenda akan dibuat pada tahap berikutnya.</p>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-950">Status Fondasi</h3>
                <div class="mt-5 space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-600"></span>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Auth Breeze aktif</p>
                            <p class="text-xs text-slate-500">Login dan profile tetap memakai alur Breeze.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-600"></span>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Layout admin siap</p>
                            <p class="text-xs text-slate-500">Sidebar, topbar, dan konten utama sudah responsif.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Modul belum dibuat</p>
                            <p class="text-xs text-slate-500">CRUD dan fitur lanjutan sengaja belum ditambahkan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
