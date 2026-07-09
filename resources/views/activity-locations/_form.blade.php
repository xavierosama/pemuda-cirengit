@csrf

@php
    $inputClass = 'mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helperClass = 'mt-1 text-xs text-slate-500';
    $errorClass = 'mt-2 text-sm text-red-600';
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="mb-5 border-b border-slate-100 pb-4">
        <h3 class="text-base font-bold text-slate-950">Informasi Lokasi</h3>
        <p class="mt-1 text-sm text-slate-500">Kelola titik lokasi yang dapat dipakai sebagai default pada Jadwal Agenda.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="name" class="{{ $labelClass }}">Nama Lokasi</label>
            <input id="name" name="name" type="text" value="{{ old('name', $activityLocation->name ?? '') }}" class="{{ $inputClass }}" required autofocus>
            @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="is_active" class="{{ $labelClass }}">Status Aktif</label>
            <select id="is_active" name="is_active" class="{{ $inputClass }}" required>
                <option value="1" @selected((string) old('is_active', isset($activityLocation) ? (int) $activityLocation->is_active : 1) === '1')>Aktif</option>
                <option value="0" @selected((string) old('is_active', isset($activityLocation) ? (int) $activityLocation->is_active : 1) === '0')>Nonaktif</option>
            </select>
            <p class="{{ $helperClass }}">Lokasi nonaktif tidak muncul di dropdown Jadwal Agenda baru.</p>
            @error('is_active') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="address" class="{{ $labelClass }}">Alamat / Keterangan Lokasi</label>
            <textarea id="address" name="address" rows="4" class="{{ $inputClass }}">{{ old('address', $activityLocation->address ?? '') }}</textarea>
            @error('address') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="latitude" class="{{ $labelClass }}">Latitude</label>
            <input id="latitude" name="latitude" type="number" step="0.0000001" value="{{ old('latitude', $activityLocation->latitude ?? '') }}" class="{{ $inputClass }}">
            <p class="{{ $helperClass }}">Opsional, isi sesuai titik lokasi presensi.</p>
            @error('latitude') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="longitude" class="{{ $labelClass }}">Longitude</label>
            <input id="longitude" name="longitude" type="number" step="0.0000001" value="{{ old('longitude', $activityLocation->longitude ?? '') }}" class="{{ $inputClass }}">
            <p class="{{ $helperClass }}">Opsional, isi sesuai titik lokasi presensi.</p>
            @error('longitude') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="radius_meters" class="{{ $labelClass }}">Radius Presensi Default</label>
            <input id="radius_meters" name="radius_meters" type="number" min="1" value="{{ old('radius_meters', $activityLocation->radius_meters ?? 100) }}" class="{{ $inputClass }}" required>
            <p class="{{ $helperClass }}">Dalam meter. Nilai ini akan mengisi radius default Jadwal Agenda.</p>
            @error('radius_meters') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:justify-end">
    <a href="{{ route('activity-locations.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Batal/Kembali</a>
    <x-ui.submit-button loading-text="Menyimpan...">Simpan</x-ui.submit-button>
</div>
