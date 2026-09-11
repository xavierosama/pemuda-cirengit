@csrf
@php
    $inputClass = 'mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
@endphp

<x-ui.card padding="md">
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="name" class="text-sm font-semibold text-slate-700">Nama Kategori</label>
            <input id="name" name="name" value="{{ old('name', $category->name) }}" class="{{ $inputClass }}" required>
            @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="slug" class="text-sm font-semibold text-slate-700">Slug</label>
            <input id="slug" name="slug" value="{{ old('slug', $category->slug) }}" class="{{ $inputClass }}" placeholder="otomatis-jika-kosong">
            @error('slug') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="type" class="text-sm font-semibold text-slate-700">Tipe</label>
            <select id="type" name="type" class="{{ $inputClass }}" required>
                @foreach (\App\Models\FinancialCategory::TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(old('type', $category->type ?: 'income') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('type') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="sort_order" class="text-sm font-semibold text-slate-700">Urutan</label>
            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $category->sort_order) }}" class="{{ $inputClass }}">
            @error('sort_order') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="color" class="text-sm font-semibold text-slate-700">Warna/Token</label>
            <input id="color" name="color" value="{{ old('color', $category->color) }}" class="{{ $inputClass }}" placeholder="emerald, sky, amber">
            @error('color') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="icon" class="text-sm font-semibold text-slate-700">Icon</label>
            <input id="icon" name="icon" value="{{ old('icon', $category->icon) }}" class="{{ $inputClass }}" placeholder="Opsional">
            @error('icon') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
            <label for="description" class="text-sm font-semibold text-slate-700">Deskripsi</label>
            <textarea id="description" name="description" rows="3" class="{{ $inputClass }}">{{ old('description', $category->description) }}</textarea>
            @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <label class="inline-flex items-center gap-2 md:col-span-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->exists ? $category->is_active : true)) class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
            <span class="text-sm font-semibold text-slate-700">Kategori aktif dan tampil di form transaksi baru</span>
        </label>
    </div>
</x-ui.card>

<div class="mt-4 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('finance.categories.index') }}" class="inline-flex justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
    <x-ui.submit-button>Simpan Kategori</x-ui.submit-button>
</div>
