@extends('layouts.admin')

@section('title', 'Edit Data Jabatan - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Edit Data Jabatan')

@section('content')
    <div class="max-w-3xl">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('positions.update', $position) }}">
                @method('PUT')
                @include('positions._form', ['position' => $position])
            </form>
        </div>
    </div>
@endsection
