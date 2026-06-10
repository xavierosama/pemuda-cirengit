@extends('layouts.admin')

@section('title', 'Profil Anggota - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Profil Anggota')

@section('content')
    @php
        $statusLabels = ['active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'alumni' => 'Alumni', 'moved' => 'Pindah'];
        $statusClasses = [
            'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'inactive' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'alumni' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'moved' => 'bg-amber-50 text-amber-700 ring-amber-200',
        ];
    @endphp

    <div class="max-w-5xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('members.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Kembali ke Data Anggota</a>
            <a href="{{ route('members.edit', $member) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">Edit Anggota</a>
        </div>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-xl font-bold uppercase text-white">{{ str($member->full_name)->substr(0, 1) }}</div>
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">{{ $member->member_number ?: 'Anggota' }}</p>
                        <h2 class="mt-1 text-2xl font-bold text-slate-950">{{ $member->full_name }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Bergabung {{ $member->joined_at?->format('d M Y') ?? '-' }}</p>
                    </div>
                </div>
                <span class="{{ $statusClasses[$member->member_status] }} inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$member->member_status] }}</span>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-950">Penempatan Organisasi</h3>
                <dl class="mt-5 space-y-4">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bidang</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $member->department?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jabatan</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $member->position?->name ?? '-' }}</dd></div>
                </dl>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-950">Kontak</h3>
                <dl class="mt-5 space-y-4">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nomor HP</dt><dd class="mt-1 text-sm text-slate-700">{{ $member->phone ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</dt><dd class="mt-1 break-all text-sm text-slate-700">{{ $member->email ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Alamat</dt><dd class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $member->address ?: '-' }}</dd></div>
                </dl>
            </section>
        </div>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-950">Catatan</h3>
            <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $member->notes ?: 'Tidak ada catatan.' }}</p>
        </section>
    </div>
@endsection
