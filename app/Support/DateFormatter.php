<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Throwable;

class DateFormatter
{
    public static function date(mixed $value, string $fallback = '-'): string
    {
        $date = self::toCarbon($value);

        return $date ? $date->format('d/m/Y') : $fallback;
    }

    public static function dateTime(mixed $value, string $fallback = '-'): string
    {
        $date = self::toCarbon($value);

        return $date ? $date->format('d/m/Y H:i') : $fallback;
    }

    public static function time(mixed $value, string $fallback = '-'): string
    {
        if (blank($value)) {
            return $fallback;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('H:i');
        }

        $time = (string) $value;

        return strlen($time) >= 5 ? substr($time, 0, 5) : $fallback;
    }

    public static function dateRange(mixed $startDate, mixed $endDate, string $separator = ' - ', string $fallback = '-'): string
    {
        $start = self::date($startDate, '');
        $end = self::date($endDate, '');

        if ($start === '' && $end === '') {
            return $fallback;
        }

        return trim($start.$separator.$end, " \t\n\r\0\x0B-");
    }

    public static function normalizeInputDate(mixed $value): mixed
    {
        if (blank($value)) {
            return null;
        }

        return self::strictNormalizedDate($value) ?? $value;
    }

    public static function normalizeInputDateForValidation(mixed $value, string $invalidValue = 'invalid-date'): ?string
    {
        if (blank($value)) {
            return null;
        }

        return self::strictNormalizedDate($value) ?? $invalidValue;
    }

    private static function strictNormalizedDate(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d');
        }

        $text = trim((string) $value);

        foreach (['d/m/Y', 'Y-m-d'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $text, config('app.timezone'));

                if ($date && $date->format($format) === $text) {
                    return $date->format('Y-m-d');
                }
            } catch (Throwable) {
                //
            }
        }

        return null;
    }

    private static function toCarbon(mixed $value): ?CarbonInterface
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value;
        }

        try {
            return Carbon::parse((string) $value, config('app.timezone'));
        } catch (Throwable) {
            return null;
        }
    }
}
