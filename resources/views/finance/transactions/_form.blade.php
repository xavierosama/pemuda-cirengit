@csrf
@php
    $inputClass = 'mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
    $selectedType = old('type', $transaction->type ?: request('type', 'income'));
@endphp

<x-ui.card padding="md">
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="type" class="text-sm font-semibold text-slate-700">Tipe Transaksi</label>
            <select id="type" name="type" class="{{ $inputClass }}" required>
                @foreach (\App\Models\FinancialTransaction::TYPES as $value => $label)
                    <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('type') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="financial_category_id" class="text-sm font-semibold text-slate-700">Kategori</label>
            <select id="financial_category_id" name="financial_category_id" class="{{ $inputClass }}" required>
                <option value="">Pilih kategori</option>
                @foreach ($categories->groupBy('type') as $type => $items)
                    <optgroup label="{{ \App\Models\FinancialCategory::TYPES[$type] ?? str($type)->headline() }}">
                        @foreach ($items as $category)
                            <option value="{{ $category->id }}" @selected((int) old('financial_category_id', $transaction->financial_category_id) === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Pilih kategori yang sesuai dengan tipe transaksi.</p>
            @error('financial_category_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="title" class="text-sm font-semibold text-slate-700">Judul Transaksi</label>
            <input id="title" name="title" value="{{ old('title', $transaction->title) }}" class="{{ $inputClass }}" required>
            @error('title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="amount" class="text-sm font-semibold text-slate-700">Nominal</label>
            <input id="amount" name="amount" type="number" min="1" step="1" value="{{ old('amount', $transaction->amount) }}" class="{{ $inputClass }}" required>
            @error('amount') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="transaction_date" class="text-sm font-semibold text-slate-700">Tanggal Transaksi</label>
            <input id="transaction_date" name="transaction_date" type="date" value="{{ old('transaction_date', optional($transaction->transaction_date)->format('Y-m-d') ?? now()->toDateString()) }}" class="{{ $inputClass }}" required>
            @error('transaction_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="payment_method" class="text-sm font-semibold text-slate-700">Metode</label>
            <select id="payment_method" name="payment_method" class="{{ $inputClass }}">
                <option value="">Tidak dicatat</option>
                @foreach (\App\Models\FinancialTransaction::PAYMENT_METHODS as $value => $label)
                    <option value="{{ $value }}" @selected(old('payment_method', $transaction->payment_method) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('payment_method') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="reference_no" class="text-sm font-semibold text-slate-700">Nomor Referensi</label>
            <input id="reference_no" name="reference_no" value="{{ old('reference_no', $transaction->reference_no) }}" class="{{ $inputClass }}" placeholder="Opsional">
            @error('reference_no') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="description" class="text-sm font-semibold text-slate-700">Deskripsi</label>
            <textarea id="description" name="description" rows="4" class="{{ $inputClass }}" placeholder="Catatan singkat transaksi.">{{ old('description', $transaction->description) }}</textarea>
            @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
</x-ui.card>

<div class="mt-4 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('finance.transactions.index', ['type' => $selectedType]) }}" class="inline-flex justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
    <x-ui.submit-button>Simpan Transaksi</x-ui.submit-button>
</div>
