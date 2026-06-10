@extends('layouts.admin')

@section('title', 'Tambah Data Anggota - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Tambah Data Anggota')

@section('content')
    <div class="max-w-4xl">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('members.store') }}">
                @include('members._form')
            </form>
        </div>
    </div>
@endsection
