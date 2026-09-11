@extends('layouts.admin')

@section('title', 'Tambah Kategori Kajian - Pemuda Cirengit')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Kategori Kajian', 'url' => route('article-categories.index')],
        ['label' => 'Tambah Kategori'],
    ]" />
@endsection

@section('content')
    <div class="space-y-4 sm:space-y-6">
        <x-ui.page-header title="Tambah Kategori Kajian" eyebrow="Publikasi" description="Buat kategori untuk mengelompokkan artikel dan hasil kajian." />
        <form method="POST" action="{{ route('article-categories.store') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @include('article-categories._form')
        </form>
    </div>
@endsection
