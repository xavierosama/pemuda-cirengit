@extends('layouts.admin')

@section('title', 'Detail Notifikasi - Pemuda Cirengit')
@section('section', 'Komunikasi')
@section('page-title', 'Detail Notifikasi')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Notifikasi', 'url' => route('admin-notifications.index')],
        ['label' => 'Detail'],
    ]" />
@endsection

@section('content')
    @php
        $statusClasses = [
            'draft' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'sent' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'cancelled' => 'bg-slate-100 text-slate-600 ring-slate-200',
        ];
    @endphp

    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Komunikasi Anggota</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">{{ $adminNotification->title }}</h2>
                    <p class="mt-2 max-w-3xl whitespace-pre-line text-sm leading-6 text-slate-600">{{ $adminNotification->message }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin-notifications.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Kembali</a>
                    @if ($adminNotification->isDraft())
                        <a href="{{ route('admin-notifications.edit', $adminNotification) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">Edit Draft</a>
                        <div x-data="{ open: false, submitting: false }" x-on:confirmed="submitting = true; $refs.sendForm.submit()">
                            <form x-ref="sendForm" method="POST" action="{{ route('admin-notifications.send', $adminNotification) }}" x-on:submit.prevent="open = true">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800" x-bind:disabled="submitting">
                                    Kirim Notifikasi
                                </button>
                            </form>
                            <x-ui.confirm-modal
                                title="Kirim Notifikasi?"
                                description="Notifikasi akan dibuat untuk target anggota dan tidak bisa diedit setelah terkirim."
                                confirm-text="Kirim"
                                loading-text="Mengirim..."
                            />
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
                <h3 class="text-base font-bold text-slate-950">Detail Pengiriman</h3>
                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Status</dt>
                        <dd class="mt-1">
                            <span class="{{ $statusClasses[$adminNotification->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $adminNotification->statusLabel() }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Tipe</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $adminNotification->typeLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Target</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $adminNotification->targetLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Channel</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $adminNotification->channelLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">URL</dt>
                        <dd class="mt-1 break-words text-sm text-slate-700">{{ $adminNotification->url ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Dibuat Oleh</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $adminNotification->creator?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Dibuat</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ \App\Support\DateFormatter::dateTime($adminNotification->created_at) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Dikirim</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ \App\Support\DateFormatter::dateTime($adminNotification->sent_at) }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-bold text-slate-950">Ringkasan</h3>
                <dl class="mt-5 space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-sm text-slate-500">Target ditemukan</dt>
                        <dd class="text-lg font-bold text-slate-950">{{ number_format($adminNotification->target_count) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-sm text-slate-500">In-app dibuat</dt>
                        <dd class="text-lg font-bold text-emerald-700">{{ number_format($personalNotificationCount) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-sm text-slate-500">Dilewati</dt>
                        <dd class="text-lg font-bold text-amber-700">{{ number_format($adminNotification->skipped_count) }}</dd>
                    </div>
                    <div class="border-t border-slate-100 pt-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Push</p>
                        <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-lg font-bold text-slate-950">{{ number_format($adminNotification->push_attempted_count) }}</p>
                                <p class="text-[11px] text-slate-500">Device</p>
                            </div>
                            <div class="rounded-xl bg-emerald-50 p-3">
                                <p class="text-lg font-bold text-emerald-700">{{ number_format($adminNotification->push_sent_count) }}</p>
                                <p class="text-[11px] text-slate-500">Terkirim</p>
                            </div>
                            <div class="rounded-xl bg-red-50 p-3">
                                <p class="text-lg font-bold text-red-700">{{ number_format($adminNotification->push_failed_count) }}</p>
                                <p class="text-[11px] text-slate-500">Gagal</p>
                            </div>
                        </div>
                    </div>
                </dl>
            </section>
        </div>
    </div>
@endsection
