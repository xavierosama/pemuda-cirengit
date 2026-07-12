@props([
    'field',
    'label',
    'currentSort' => null,
    'currentDirection' => 'asc',
    'query' => [],
    'align' => 'left',
    'sortParam' => 'sort',
    'directionParam' => 'direction',
    'pageParam' => 'page',
])

@php
    $isActive = $currentSort === $field;
    $nextDirection = $isActive && $currentDirection === 'asc' ? 'desc' : 'asc';
    $nextQuery = array_merge($query, [$sortParam => $field, $directionParam => $nextDirection]);
    unset($nextQuery[$pageParam]);
    $url = url()->current().'?'.http_build_query($nextQuery);
    $alignClass = $align === 'right' ? 'justify-end text-right' : 'justify-start text-left';
@endphp

<th {{ $attributes->merge(['class' => 'whitespace-nowrap bg-slate-50 px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500 dark:bg-slate-900 dark:text-slate-400']) }}>
    <a href="{{ $url }}" class="{{ $alignClass }} inline-flex w-full items-center gap-1.5 transition hover:text-slate-800 focus:outline-none focus:text-emerald-700 dark:hover:text-slate-200">
        <span>{{ $label }}</span>
        <span class="{{ $isActive ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-400' }}" aria-hidden="true">
            @if ($isActive && $currentDirection === 'asc')
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 4.25a.75.75 0 0 1 .53.22l4 4a.75.75 0 1 1-1.06 1.06L10.75 6.81V15a.75.75 0 0 1-1.5 0V6.81L6.53 9.53a.75.75 0 0 1-1.06-1.06l4-4a.75.75 0 0 1 .53-.22Z" clip-rule="evenodd" />
                </svg>
            @elseif ($isActive)
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 15.75a.75.75 0 0 1-.53-.22l-4-4a.75.75 0 1 1 1.06-1.06l2.72 2.72V5a.75.75 0 0 1 1.5 0v8.19l2.72-2.72a.75.75 0 1 1 1.06 1.06l-4 4a.75.75 0 0 1-.53.22Z" clip-rule="evenodd" />
                </svg>
            @else
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3.75a.75.75 0 0 1 .53.22l2.5 2.5a.75.75 0 1 1-1.06 1.06L10 5.56 8.03 7.53a.75.75 0 0 1-1.06-1.06l2.5-2.5a.75.75 0 0 1 .53-.22ZM6.97 12.47a.75.75 0 0 1 1.06 0L10 14.44l1.97-1.97a.75.75 0 1 1 1.06 1.06l-2.5 2.5a.75.75 0 0 1-1.06 0l-2.5-2.5a.75.75 0 0 1 0-1.06Z" />
                </svg>
            @endif
        </span>
    </a>
</th>
