@props([
    'items' => [],
])

@if (count($items) > 0)
    <nav {{ $attributes->merge(['class' => 'overflow-x-auto']) }} aria-label="Breadcrumb">
        <ol class="flex min-w-0 items-center gap-2 whitespace-nowrap text-sm text-slate-500">
            @foreach ($items as $item)
                @php
                    $isCurrent = $loop->last || empty($item['url']);
                @endphp

                <li class="flex min-w-0 items-center gap-2">
                    @if (! $loop->first)
                        <svg class="h-4 w-4 shrink-0 text-slate-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.22 14.78a.75.75 0 0 1 0-1.06L10.94 10 7.22 6.28a.75.75 0 1 1 1.06-1.06l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd" />
                        </svg>
                    @endif

                    @if ($isCurrent)
                        <span class="max-w-[12rem] truncate font-semibold text-slate-700" aria-current="page">{{ $item['label'] }}</span>
                    @else
                        <a href="{{ $item['url'] }}" class="max-w-[12rem] truncate font-medium text-slate-500 transition hover:text-emerald-700">
                            {{ $item['label'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
