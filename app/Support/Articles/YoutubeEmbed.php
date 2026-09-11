<?php

namespace App\Support\Articles;

class YoutubeEmbed
{
    public static function videoId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');

        if (str_contains($host, 'youtu.be')) {
            return self::cleanId(trim($parts['path'] ?? '', '/'));
        }

        if (! str_contains($host, 'youtube.com')) {
            return null;
        }

        parse_str($parts['query'] ?? '', $query);

        if (! empty($query['v'])) {
            return self::cleanId($query['v']);
        }

        $path = trim($parts['path'] ?? '', '/');
        if (str_starts_with($path, 'embed/') || str_starts_with($path, 'shorts/')) {
            return self::cleanId(substr($path, strpos($path, '/') + 1));
        }

        return null;
    }

    public static function embedUrl(?string $url): ?string
    {
        $videoId = self::videoId($url);

        return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
    }

    private static function cleanId(string $value): ?string
    {
        $value = trim($value);

        return preg_match('/^[A-Za-z0-9_-]{6,20}$/', $value) ? $value : null;
    }
}
