@props([
    'perPage' => 10,
    'options' => [10, 25, 50, 100],
    'query' => [],
    'id' => null,
])

@php
    $selectId = $id ?: 'per_page_'.md5(url()->current().json_encode($query));
@endphp

<form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2 text-sm">
    @foreach ($query as $key => $value)
        @continue(in_array($key, ['per_page', 'page'], true) || is_array($value))
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach
    <label for="{{ $selectId }}" class="font-semibold text-slate-600">Tampilkan</label>
    <select id="{{ $selectId }}" name="per_page" onchange="this.form.submit()" class="rounded-lg border-slate-300 py-1.5 pl-3 pr-8 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
        @foreach ($options as $option)
            <option value="{{ $option }}" @selected((int) $perPage === (int) $option)>{{ $option }}</option>
        @endforeach
    </select>
    <span class="text-slate-500">data</span>
</form>
