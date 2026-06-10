@extends('layouts.admin')

@section('title', 'Edit Data Anggota - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Edit Data Anggota')

@section('content')
    <div class="max-w-4xl">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('members.update', $member) }}">
                @method('PUT')
                @include('members._form')
            </form>
        </div>
    </div>
@endsection
