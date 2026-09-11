@extends('layouts.admin')

@section('title', 'Detail Kategori Kajian - Pemuda Cirengit')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Kategori Kajian', 'url' => route('article-categories.index')],
        ['label' => 'Detail Kategori'],
    ]" />
@endsection

@section('content')
    <div class="max-w-5xl space-y-4 sm:space-y-6">
        <x-ui.page-header title="{{ $articleCategory->name }}" eyebrow="Kategori Kajian" description="{{ $articleCategory->description ?: 'Kategori kajian Pemuda Cirengit.' }}">
            <x-slot:action>
                <x-ui.button :href="route('article-categories.edit', $articleCategory)" variant="secondary">Edit Kategori</x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        <x-ui.card padding="md">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Slug</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $articleCategory->slug }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Status</dt><dd class="mt-1"><x-ui.status-badge :status="$articleCategory->is_active ? 'active' : 'inactive'" :label="$articleCategory->is_active ? 'Aktif' : 'Nonaktif'" /></dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Urutan</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $articleCategory->sort_order ?? '-' }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Jumlah Artikel</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ number_format($articleCategory->articles_count) }}</dd></div>
            </dl>
        </x-ui.card>
    </div>
@endsection
