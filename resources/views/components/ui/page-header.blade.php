@props([
    'title',
    'description' => null,
    'eyebrow' => null,
])

<x-ui.card padding="lg">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            @if ($eyebrow)
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700 ring-1 ring-inset ring-emerald-100">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    {{ $eyebrow }}
                </div>
            @endif
            <h2 class="{{ $eyebrow ? 'mt-3' : '' }} text-2xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-3xl">{{ $title }}</h2>
            @if ($description)
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $description }}</p>
            @endif
        </div>

        @isset($action)
            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end">
                {{ $action }}
            </div>
        @endisset
    </div>
</x-ui.card>
