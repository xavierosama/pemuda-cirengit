@extends('layouts.admin')

@section('title', 'Detail Data Bidang - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Detail Data Bidang')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('departments.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Kembali ke Data Bidang</a>
            <a href="{{ route('departments.edit', $department) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">
                Edit Bidang
            </a>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Bidang</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">{{ $department->name }}</h2>
                </div>
                <span class="{{ $department->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">
                    {{ ucfirst($department->status) }}
                </span>
            </div>

            <dl class="mt-6 grid gap-5 border-t border-slate-200 pt-6">
                <div>
                    <dt class="text-sm font-semibold text-slate-700">Deskripsi</dt>
                    <dd class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $department->description ?: '-' }}</dd>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-semibold text-slate-700">Dibuat</dt>
                        <dd class="mt-2 text-sm text-slate-600">{{ $department->created_at?->format('d M Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-slate-700">Diperbarui</dt>
                        <dd class="mt-2 text-sm text-slate-600">{{ $department->updated_at?->format('d M Y H:i') }}</dd>
                    </div>
                </div>
            </dl>
        </div>
    </div>
@endsection
