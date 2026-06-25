@extends('layouts.admin')

@section('title', 'Input Kehadiran Manual - Pemuda Cirengit')
@section('section', 'Presensi')
@section('page-title', 'Input Kehadiran Manual')

@section('content')
    @php
        $statusLabels = ['present' => 'Hadir', 'permission' => 'Izin', 'absent' => 'Tidak Hadir', 'need_verification' => 'Perlu Verifikasi'];
    @endphp

    <div class="mx-auto max-w-3xl space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Daftar Hadir Manual</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-950">{{ $activity->title }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ \App\Support\DateFormatter::date($activity->activity_date) }}</p>
            </div>
            <a href="{{ route('activities.attendances.index', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Kembali ke Daftar Hadir
            </a>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-950">Input Kehadiran</h3>
                <p class="mt-1 text-sm text-slate-500">Pilih anggota aktif lalu tetapkan status kehadiran secara manual.</p>
            </div>

            <form method="POST" action="{{ route('activities.attendances.store', $activity) }}" class="mt-5 space-y-5" x-data="{ submitting: false }" x-on:submit="submitting = true">
                @csrf
                <input type="hidden" name="activity_id" value="{{ $activity->id }}">

                <div>
                    <label for="member_id" class="block text-sm font-semibold text-slate-700">Anggota</label>
                    <select id="member_id" name="member_id" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                        <option value="">Pilih anggota aktif</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" @selected((string) old('member_id') === (string) $member->id)>{{ $member->full_name }}</option>
                        @endforeach
                    </select>
                    @error('member_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700">Status Kehadiran</label>
                    <select id="status" name="status" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                        @foreach ($statuses as $statusValue)
                            <option value="{{ $statusValue }}" @selected(old('status', 'present') === $statusValue)>{{ $statusLabels[$statusValue] }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-slate-700">Catatan</label>
                    <textarea id="notes" name="notes" rows="4" class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600" placeholder="Catatan opsional">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <a href="{{ route('activities.attendances.index', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</a>
                    <x-ui.submit-button size="lg" loading-text="Menyimpan...">Simpan Kehadiran</x-ui.submit-button>
                </div>
            </form>
        </section>
    </div>
@endsection
