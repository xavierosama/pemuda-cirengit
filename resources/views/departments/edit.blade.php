@extends('layouts.admin')

@section('title', 'Edit Data Bidang - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Edit Data Bidang')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Master Data</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Edit Bidang</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Perbarui nama, deskripsi, dan status bidang organisasi.</p>
                    @if ($department->updated_at)
                        <p class="mt-3 text-xs font-medium text-slate-500">Terakhir diperbarui: {{ $department->updated_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
                <a href="{{ route('departments.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Kembali</a>
            </div>
        </div>

        <form method="POST" action="{{ route('departments.update', $department) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @method('PUT')
            @include('departments._form', ['department' => $department])
        </form>
    </div>
@endsection
