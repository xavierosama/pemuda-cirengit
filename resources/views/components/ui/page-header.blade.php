@props([
    'title',
    'description' => null,
    'eyebrow' => null,
])

<x-ui.card padding="lg">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            @if ($eyebrow)
                <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">{{ $eyebrow }}</p>
            @endif
            <h2 class="{{ $eyebrow ? 'mt-2' : '' }} text-2xl font-bold text-slate-950">{{ $title }}</h2>
            @if ($description)
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ $description }}</p>
            @endif
        </div>

        @isset($action)
            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end">
                {{ $action }}
            </div>
        @endisset
    </div>
</x-ui.card>
