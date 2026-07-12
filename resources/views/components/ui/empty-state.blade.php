@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'px-4 py-12 text-center']) }}>
    <div class="mx-auto max-w-sm">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 ring-1 ring-inset ring-sky-100 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/20">
            @isset($icon)
                {{ $icon }}
            @else
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h8M8 11h8M8 15h5M5 4.75h14A1.25 1.25 0 0 1 20.25 6v12A1.25 1.25 0 0 1 19 19.25H5A1.25 1.25 0 0 1 3.75 18V6A1.25 1.25 0 0 1 5 4.75Z" />
                </svg>
            @endisset
        </div>
        <p class="mt-4 text-base font-bold text-slate-900 dark:text-white">{{ $title }}</p>
        @if ($description)
            <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif

        @isset($action)
            <div class="mt-5 flex justify-center">
                {{ $action }}
            </div>
        @endisset
    </div>
</div>
