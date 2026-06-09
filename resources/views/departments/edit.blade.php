@extends('layouts.admin')

@section('title', 'Edit Data Bidang - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Edit Data Bidang')

@section('content')
    <div class="max-w-3xl">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('departments.update', $department) }}">
                @method('PUT')
                @include('departments._form', ['department' => $department])
            </form>
        </div>
    </div>
@endsection
