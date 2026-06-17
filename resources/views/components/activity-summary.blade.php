@props([
    'activity',
    'showMeta' => true,
    'titleClass' => 'text-sm font-semibold text-slate-900',
    'subClass' => 'mt-1 text-xs text-slate-500',
    'metaClass' => 'mt-1 text-xs text-slate-500',
])

@php
    $dayLabels = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $subInfo = $activity->topic ?: ($activity->description ?: $activity->location);
    $time = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '').($activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : ''));
    $dateLabel = $activity->activity_date
        ? ($dayLabels[$activity->activity_date->dayOfWeek].', '.$activity->activity_date->format('d/m/Y'))
        : null;
    $metaItems = array_filter([
        $dateLabel,
        $time !== '' ? $time : null,
        $activity->location,
    ]);
@endphp

<div {{ $attributes->merge(['class' => 'min-w-0']) }}>
    <p class="line-clamp-2 break-words {{ $titleClass }}">{{ $activity->title }}</p>
    @if ($subInfo)
        <p class="line-clamp-2 break-words {{ $subClass }}">{{ $activity->topic ? 'Topik: '.$activity->topic : $subInfo }}</p>
    @endif
    @if ($showMeta && count($metaItems) > 0)
        <p class="line-clamp-1 break-words {{ $metaClass }}">{{ implode(' - ', $metaItems) }}</p>
    @endif
</div>
