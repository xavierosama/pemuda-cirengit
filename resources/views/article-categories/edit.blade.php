@extends('layouts.admin')

@section('title', 'Edit Kategori Kajian - Pemuda Cirengit')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Kategori Kajian', 'url' => route('article-categories.index')],
        ['label' => 'Edit Kategori'],
    ]" />
@endsection

@section('content')
    <div class="space-y-4 sm:space-y-6">
        <x-ui.page-header title="Edit Kategori Kajian" eyebrow="Publikasi" description="Perbarui nama, slug, deskripsi, dan status kategori." />
        <form method="POST" action="{{ route('article-categories.update', $articleCategory) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @method('PUT')
            @include('article-categories._form')
        </form>
    </div>
@endsection
