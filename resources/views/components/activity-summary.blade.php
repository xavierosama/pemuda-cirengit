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
    $startTime = \App\Support\DateFormatter::time($activity->start_time, '');
    $endTime = \App\Support\DateFormatter::time($activity->end_time, '');
    $time = trim($startTime.($endTime !== '' ? ' - '.$endTime : ''));
    $dateLabel = $activity->activity_date
        ? ($dayLabels[$activity->activity_date->dayOfWeek].', '.\App\Support\DateFormatter::date($activity->activity_date))
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
