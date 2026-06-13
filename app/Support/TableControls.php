<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TableControls
{
    public const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public static function direction(Request $request): string
    {
        $direction = $request->string('direction')->lower()->toString();

        return in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';
    }

    public static function sort(Request $request, array $allowedSorts): ?string
    {
        $sort = $request->string('sort')->toString();

        return array_key_exists($sort, $allowedSorts) ? $sort : null;
    }

    public static function perPage(Request $request, int $default = 10): int
    {
        $perPage = $request->integer('per_page');

        return in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : $default;
    }

    public static function applySort(Builder $query, ?string $sort, string $direction, array $allowedSorts, callable $defaultSort): Builder
    {
        if ($sort && isset($allowedSorts[$sort])) {
            return $query->orderBy($allowedSorts[$sort], $direction);
        }

        $defaultSort($query);

        return $query;
    }

    public static function viewData(Request $request, ?string $sort, string $direction, int $perPage): array
    {
        return [
            'currentSort' => $sort,
            'currentDirection' => $direction,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'queryParams' => $request->query(),
        ];
    }
}
