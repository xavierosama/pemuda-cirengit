@csrf

@php
    $inputClass = 'mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helperClass = 'mt-1 text-xs text-slate-500';
    $errorClass = 'mt-2 text-sm text-red-600';
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="mb-5 border-b border-slate-100 pb-4">
        <h3 class="text-base font-bold text-slate-950">Informasi Bidang</h3>
        <p class="mt-1 text-sm text-slate-500">Isi nama, deskripsi, dan status bidang organisasi.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="name" class="{{ $labelClass }}">Nama Bidang</label>
            <input id="name" name="name" type="text" value="{{ old('name', $department->name ?? '') }}" class="{{ $inputClass }}" required autofocus>
            @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="status" class="{{ $labelClass }}">Status</label>
            <select id="status" name="status" class="{{ $inputClass }}" required>
                @foreach (['active' => 'Aktif', 'inactive' => 'Nonaktif'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $department->status ?? 'active') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="{{ $helperClass }}">Pilih aktif jika bidang masih digunakan.</p>
            @error('status') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="description" class="{{ $labelClass }}">Deskripsi</label>
            <textarea id="description" name="description" rows="5" class="{{ $inputClass }}">{{ old('description', $department->description ?? '') }}</textarea>
            <p class="{{ $helperClass }}">Tuliskan keterangan singkat tentang ruang lingkup bidang.</p>
            @error('description') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:justify-end">
    <a href="{{ route('departments.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Batal/Kembali</a>
    <x-ui.submit-button loading-text="Menyimpan...">Simpan</x-ui.submit-button>
</div>
