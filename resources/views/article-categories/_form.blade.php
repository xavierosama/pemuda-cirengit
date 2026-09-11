@csrf

@php
    $inputClass = 'mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helperClass = 'mt-1 text-xs text-slate-500';
@endphp

<x-ui.card padding="md">
    <div class="mb-5 border-b border-slate-100 pb-4">
        <h3 class="text-base font-bold text-slate-950">Informasi Kategori</h3>
        <p class="mt-1 text-sm text-slate-500">Slug boleh dikosongkan untuk dibuat otomatis dari nama kategori.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="name" class="{{ $labelClass }}">Nama Kategori</label>
            <input id="name" name="name" type="text" value="{{ old('name', $articleCategory->name ?? '') }}" class="{{ $inputClass }}" required autofocus>
            @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="slug" class="{{ $labelClass }}">Slug</label>
            <input id="slug" name="slug" type="text" value="{{ old('slug', $articleCategory->slug ?? '') }}" class="{{ $inputClass }}" placeholder="otomatis-jika-kosong">
            @error('slug') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="color" class="{{ $labelClass }}">Warna/Label</label>
            <input id="color" name="color" type="text" value="{{ old('color', $articleCategory->color ?? '') }}" class="{{ $inputClass }}" placeholder="emerald / sky / amber">
            <p class="{{ $helperClass }}">Opsional untuk pengembangan visual kategori berikutnya.</p>
            @error('color') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="sort_order" class="{{ $labelClass }}">Urutan</label>
            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $articleCategory->sort_order ?? '') }}" class="{{ $inputClass }}">
            @error('sort_order') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="description" class="{{ $labelClass }}">Deskripsi</label>
            <textarea id="description" name="description" rows="4" class="{{ $inputClass }}">{{ old('description', $articleCategory->description ?? '') }}</textarea>
            @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <label class="inline-flex items-center gap-2 md:col-span-2">
            <input name="is_active" type="hidden" value="0">
            <input name="is_active" type="checkbox" value="1" @checked(old('is_active', isset($articleCategory) ? $articleCategory->is_active : true)) class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-600">
            <span class="text-sm font-semibold text-slate-700">Kategori aktif dan tampil di publik</span>
        </label>
    </div>
</x-ui.card>

<div class="mt-4 flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:justify-end">
    <a href="{{ route('article-categories.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal/Kembali</a>
    <x-ui.submit-button loading-text="Menyimpan...">Simpan</x-ui.submit-button>
</div>
