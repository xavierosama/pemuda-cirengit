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
    $icon = $isActive ? ($currentDirection === 'asc' ? '↑' : '↓') : '↕';
@endphp

<th {{ $attributes->merge(['class' => 'whitespace-nowrap px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500']) }}>
    <a href="{{ $url }}" class="{{ $alignClass }} inline-flex w-full items-center gap-1.5 transition hover:text-slate-800">
        <span>{{ $label }}</span>
        <span class="{{ $isActive ? 'text-emerald-700' : 'text-slate-400' }}" aria-hidden="true">{{ $icon }}</span>
    </a>
</th>
