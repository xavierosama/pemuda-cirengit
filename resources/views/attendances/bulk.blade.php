@extends('layouts.admin')

@section('title', 'Input Massal Daftar Hadir - Pemuda Cirengit')
@section('section', 'Presensi')
@section('page-title', 'Input Massal Daftar Hadir')

@section('content')
    @php $statusLabels = ['present' => 'Hadir', 'permission' => 'Izin', 'absent' => 'Tidak Hadir', 'need_verification' => 'Perlu Verifikasi']; @endphp
    <div class="space-y-6">
        <div><a href="{{ route('activities.attendances.index', $activity) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Kembali ke Daftar Hadir</a><h2 class="mt-3 text-xl font-bold text-slate-950">{{ $activity->title }}</h2><p class="mt-1 text-sm text-slate-500">Tetapkan status seluruh anggota aktif. Data yang sudah ada akan diperbarui.</p></div>

        <form method="POST" action="{{ route('activities.attendances.bulk.store', $activity) }}">
            @csrf @method('PUT')
            <input type="hidden" name="activity_id" value="{{ $activity->id }}">
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Anggota</th><th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Bidang</th><th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Status</th><th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Catatan</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($members as $index => $member)
                                @php $existing = $existingAttendances->get($member->id); @endphp
                                <tr>
                                    <td class="px-4 py-4 text-sm font-semibold text-slate-900">{{ $member->full_name }}<input type="hidden" name="attendances[{{ $index }}][member_id]" value="{{ $member->id }}"></td>
                                    <td class="px-4 py-4 text-sm text-slate-600">{{ $member->department?->name ?? '-' }}</td>
                                    <td class="px-4 py-4"><select name="attendances[{{ $index }}][status]" class="block min-w-44 rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old("attendances.$index.status", $existing?->status ?? 'absent') === $value)>{{ $label }}</option>@endforeach</select></td>
                                    <td class="px-4 py-4"><input name="attendances[{{ $index }}][notes]" type="text" value="{{ old("attendances.$index.notes", $existing?->notes) }}" placeholder="Catatan opsional" class="block min-w-56 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600"></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada anggota aktif.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @error('attendances')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
            <div class="mt-6 flex justify-end"><button type="submit" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800" @disabled($members->isEmpty())>Simpan Semua Kehadiran</button></div>
        </form>
    </div>
@endsection
