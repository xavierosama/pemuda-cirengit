@extends('layouts.admin')

@section('title', 'Input Massal Daftar Hadir - Pemuda Cirengit')
@section('section', 'Presensi')
@section('page-title', 'Input Massal Daftar Hadir')

@section('content')
    @php
        $statusLabels = ['present' => 'Hadir', 'permission' => 'Izin', 'absent' => 'Tidak Hadir', 'need_verification' => 'Perlu Verifikasi'];
    @endphp

    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Daftar Hadir Massal</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-950">{{ $activity->title }}</h2>
                <p class="mt-2 text-sm text-slate-500">Tetapkan status seluruh anggota aktif. Data yang sudah ada akan diperbarui.</p>
            </div>
            <a href="{{ route('activities.attendances.index', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Kembali ke Daftar Hadir
            </a>
        </div>

        <form method="POST" action="{{ route('activities.attendances.bulk.store', $activity) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            @method('PUT')
            <input type="hidden" name="activity_id" value="{{ $activity->id }}">

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h3 class="text-base font-bold text-slate-950">Daftar Anggota Aktif</h3>
                    <p class="mt-1 text-sm text-slate-500">Gunakan tabel ini untuk menyimpan banyak status kehadiran sekaligus.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                @foreach (['Anggota', 'Status', 'Catatan'] as $heading)
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($members as $index => $member)
                                @php
                                    $existing = $existingAttendances->get($member->id);
                                @endphp
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-4 py-4 text-sm font-semibold text-slate-900">
                                        {{ $member->full_name }}
                                        <input type="hidden" name="attendances[{{ $index }}][member_id]" value="{{ $member->id }}">
                                    </td>
                                    <td class="px-4 py-4">
                                        <select name="attendances[{{ $index }}][status]" class="block min-w-44 rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                                            @foreach ($statusLabels as $value => $label)
                                                <option value="{{ $value }}" @selected(old("attendances.$index.status", $existing?->status ?? 'absent') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-4">
                                        <input name="attendances[{{ $index }}][notes]" type="text" value="{{ old("attendances.$index.notes", $existing?->notes) }}" placeholder="Catatan opsional" class="block min-w-56 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-12 text-center">
                                        <p class="text-sm font-semibold text-slate-800">Tidak ada anggota aktif.</p>
                                        <p class="mt-1 text-sm text-slate-500">Tambahkan atau aktifkan anggota terlebih dahulu sebelum input massal.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @error('attendances')
                <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('activities.attendances.index', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</a>
                <x-ui.submit-button size="lg" loading-text="Menyimpan..." :disabled="$members->isEmpty()">Simpan Semua Kehadiran</x-ui.submit-button>
            </div>
        </form>
    </div>
@endsection
