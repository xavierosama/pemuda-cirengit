@extends('layouts.admin')

@section('title', 'Edit Artikel Kajian - Pemuda Cirengit')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Artikel Kajian', 'url' => route('articles.index')],
        ['label' => 'Edit Artikel'],
    ]" />
@endsection

@section('content')
    <div class="space-y-4 sm:space-y-6">
        <x-ui.page-header title="Edit Artikel Kajian" eyebrow="Publikasi" description="Perbarui konten, media, dan status publikasi artikel." />
        <form method="POST" action="{{ route('articles.update', $article) }}" enctype="multipart/form-data" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @method('PUT')
            @include('articles._form')
        </form>
    </div>
@endsection
