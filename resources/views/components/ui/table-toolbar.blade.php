@props([
    'action',
    'searchName' => 'search',
    'searchValue' => '',
    'searchPlaceholder' => 'Cari data',
    'searchLabel' => 'Search',
    'hidden' => [],
    'searchHidden' => null,
    'filterHidden' => null,
    'filterCount' => 0,
    'resetHref' => null,
    'showFilter' => false,
    'showSearch' => true,
])

@php
    $searchHidden = $searchHidden ?? $hidden;
    $filterHidden = $filterHidden ?? $hidden;
@endphp

<div
    class="flex w-full flex-col gap-3 rounded-2xl bg-slate-50/80 p-2 ring-1 ring-inset ring-slate-200/70 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end dark:bg-slate-900/70 dark:ring-slate-800"
    x-data="{ filterOpen: false }"
    x-on:keydown.escape.window="filterOpen = false"
>
    @if ($showSearch)
        <form method="GET" action="{{ $action }}" class="w-full sm:w-72 lg:w-80">
            @foreach ($searchHidden as $name => $value)
                @if ($value !== null && $value !== '')
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endif
            @endforeach

            <label for="{{ $searchName }}-toolbar" class="sr-only">{{ $searchLabel }}</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.765l2.631 2.631a.75.75 0 1 0 1.061-1.061l-2.631-2.631A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0A4 4 0 0 1 5 9Z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input
                    id="{{ $searchName }}-toolbar"
                    name="{{ $searchName }}"
                    type="search"
                    value="{{ $searchValue }}"
                    placeholder="{{ $searchPlaceholder }}"
                    class="block w-full rounded-xl border-slate-200 bg-white py-2 pl-9 pr-3 text-sm font-medium text-slate-700 shadow-sm transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-emerald-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                    x-on:input.debounce.600ms="$el.form.submit()"
                >
            </div>
        </form>
    @endif

    <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:flex-nowrap sm:justify-end">
        @if ($showFilter)
            <div class="relative">
                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/10"
                    x-on:click="filterOpen = ! filterOpen"
                    x-bind:aria-expanded="filterOpen.toString()"
                >
                    <span>Filter</span>
                    @if ($filterCount > 0)
                        <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-emerald-700 px-1.5 text-[11px] font-bold text-white">{{ $filterCount }}</span>
                    @endif
                </button>

                <div
                    x-cloak
                    x-show="filterOpen"
                    x-transition
                    x-on:click.outside="filterOpen = false"
                    class="absolute right-0 z-40 mt-2 w-[min(90vw,32rem)] rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900"
                    style="display: none;"
                >
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-bold text-slate-950 dark:text-slate-100">Filter Detail</h4>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Atur filter lalu klik Terapkan Filter.</p>
                        </div>
                        <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200" x-on:click="filterOpen = false" aria-label="Tutup filter">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <form method="GET" action="{{ $action }}" class="space-y-4" x-on:submit="filterOpen = false">
                        @foreach ($filterHidden as $name => $value)
                            @if ($value !== null && $value !== '')
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        @if ($showSearch && $searchValue !== null && $searchValue !== '')
                            <input type="hidden" name="{{ $searchName }}" value="{{ $searchValue }}">
                        @endif

                        {{ $filters }}

                        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end dark:border-slate-800">
                            @if ($resetHref)
                                <a href="{{ $resetHref }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Reset</a>
                            @endif
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 dark:focus:ring-offset-slate-900">Terapkan Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
