<?php

namespace App\Support\Articles;

use DOMDocument;
use DOMElement;
use DOMNode;

class ArticleContent
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u',
        'ul', 'ol', 'li', 'blockquote',
        'h2', 'h3', 'h4',
        'a', 'img', 'span',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'hr',
    ];

    private const DANGEROUS_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'svg', 'math',
    ];

    private const ALLOWED_FONT_SIZES = ['14px', '16px', '18px', '20px', '24px'];

    private const ALLOWED_TEXT_ALIGN = ['left', 'center', 'right'];

    public static function sanitize(?string $content): string
    {
        $content = trim((string) $content);

        if ($content === '') {
            return '';
        }

        if (! self::looksLikeHtml($content)) {
            return self::plainTextToHtml($content);
        }

        if (! class_exists(DOMDocument::class)) {
            return self::fallbackSanitize($content);
        }

        $document = new DOMDocument('1.0', 'UTF-8');

        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div id="article-content-root">'.$content.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('article-content-root');
        if (! $root) {
            return self::fallbackSanitize($content);
        }

        self::sanitizeChildren($root);

        $html = '';
        foreach ($root->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        return trim($html);
    }

    public static function toHtml(?string $content): string
    {
        return self::sanitize($content);
    }

    private static function sanitizeChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tagName = strtolower($child->tagName);

            if (in_array($tagName, self::DANGEROUS_TAGS, true)) {
                $child->parentNode?->removeChild($child);
                continue;
            }

            if (! in_array($tagName, self::ALLOWED_TAGS, true)) {
                self::unwrapNode($child);
                continue;
            }

            self::sanitizeAttributes($child);

            if ($tagName === 'img' && ! $child->hasAttribute('src')) {
                $child->parentNode?->removeChild($child);
                continue;
            }

            if (in_array($tagName, ['p', 'blockquote', 'li', 'td', 'th'], true)
                && ! $child->hasAttribute('dir')
                && self::containsArabic($child->textContent)
            ) {
                $child->setAttribute('dir', 'rtl');
            }

            self::sanitizeChildren($child);
        }
    }

    private static function unwrapNode(DOMElement $node): void
    {
        $parent = $node->parentNode;
        if (! $parent) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    private static function sanitizeAttributes(DOMElement $element): void
    {
        $tagName = strtolower($element->tagName);

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim($attribute->value);

            if (str_starts_with($name, 'on')) {
                $element->removeAttribute($attribute->name);
                continue;
            }

            if ($tagName === 'a' && $name === 'href') {
                if (! self::isSafeHref($value)) {
                    $element->removeAttribute($attribute->name);
                    continue;
                }

                $element->setAttribute('href', $value);
                $element->setAttribute('rel', 'noopener noreferrer');
                continue;
            }

            if ($tagName === 'a' && $name === 'target') {
                if (! in_array($value, ['_blank', '_self'], true)) {
                    $element->removeAttribute($attribute->name);
                }
                continue;
            }

            if ($name === 'rel' && $tagName === 'a') {
                $element->setAttribute('rel', 'noopener noreferrer');
                continue;
            }

            if ($tagName === 'img' && $name === 'src') {
                if (! self::isSafeImageSrc($value)) {
                    $element->removeAttribute($attribute->name);
                    continue;
                }

                $element->setAttribute('src', $value);
                continue;
            }

            if ($tagName === 'img' && $name === 'alt') {
                $element->setAttribute('alt', str($value)->limit(160, '')->toString());
                continue;
            }

            if ($tagName === 'img' && in_array($name, ['width', 'height'], true)) {
                if (ctype_digit($value) && (int) $value >= 1 && (int) $value <= 1600) {
                    continue;
                }

                $element->removeAttribute($attribute->name);
                continue;
            }

            if ($name === 'dir' && in_array($value, ['rtl', 'ltr', 'auto'], true)) {
                continue;
            }

            if ($name === 'class' && in_array($tagName, ['p', 'blockquote', 'li', 'td', 'th', 'span'], true)) {
                $classes = collect(preg_split('/\s+/', $value) ?: [])
                    ->filter(fn ($class) => $class === 'arabic')
                    ->values();

                if ($classes->isNotEmpty()) {
                    $element->setAttribute('class', $classes->implode(' '));
                    continue;
                }
            }

            if (in_array($tagName, ['td', 'th'], true)
                && in_array($name, ['colspan', 'rowspan'], true)
                && ctype_digit($value)
                && (int) $value >= 1
                && (int) $value <= 12
            ) {
                continue;
            }

            if ($name === 'style' && in_array($tagName, ['p', 'blockquote', 'li', 'td', 'th', 'span', 'h2', 'h3', 'h4'], true)) {
                $style = self::sanitizeStyle($value);

                if ($style !== '') {
                    $element->setAttribute('style', $style);
                    continue;
                }
            }

            $element->removeAttribute($attribute->name);
        }
    }

    private static function isSafeHref(string $href): bool
    {
        if ($href === '' || preg_match('/^\s*(javascript|data|vbscript):/i', $href)) {
            return false;
        }

        if (str_starts_with($href, '#') || str_starts_with($href, '/')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
    }

    private static function isSafeImageSrc(string $src): bool
    {
        if ($src === '' || preg_match('/^\s*(javascript|data|vbscript):/i', $src)) {
            return false;
        }

        if (str_starts_with($src, '/storage/article-content/')) {
            return true;
        }

        $path = parse_url($src, PHP_URL_PATH);

        return is_string($path) && str_starts_with($path, '/storage/article-content/');
    }

    private static function sanitizeStyle(string $style): string
    {
        $allowed = [];

        foreach (explode(';', $style) as $declaration) {
            [$property, $value] = array_pad(explode(':', $declaration, 2), 2, null);
            $property = strtolower(trim((string) $property));
            $value = trim((string) $value);
            $normalizedValue = strtolower(preg_replace('/\s+/', ' ', $value) ?? '');

            if ($property === 'font-size' && in_array($normalizedValue, self::ALLOWED_FONT_SIZES, true)) {
                $allowed[] = "font-size: {$normalizedValue}";
                continue;
            }

            if ($property === 'text-align' && in_array($normalizedValue, self::ALLOWED_TEXT_ALIGN, true)) {
                $allowed[] = "text-align: {$normalizedValue}";
                continue;
            }

            if ($property === 'font-family') {
                $fontFamily = self::sanitizeFontFamily($value);
                if ($fontFamily !== null) {
                    $allowed[] = "font-family: {$fontFamily}";
                }
            }
        }

        return implode('; ', $allowed);
    }

    private static function sanitizeFontFamily(string $value): ?string
    {
        $normalized = strtolower(str_replace(['"', "'"], '', preg_replace('/\s+/', ' ', $value) ?? ''));

        return match (true) {
            str_contains($normalized, 'system-ui') => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
            str_contains($normalized, 'georgia') => 'Georgia, serif',
            $normalized === 'serif' || str_contains($normalized, ', serif') => 'serif',
            default => null,
        };
    }

    private static function plainTextToHtml(string $content): string
    {
        $paragraphs = preg_split("/\R{2,}/", $content) ?: [];

        return collect($paragraphs)
            ->map(fn ($paragraph) => trim($paragraph))
            ->filter()
            ->map(function ($paragraph) {
                $escaped = nl2br(e($paragraph), false);
                $dir = self::containsArabic($paragraph) ? ' dir="rtl"' : '';

                return "<p{$dir}>{$escaped}</p>";
            })
            ->implode('');
    }

    private static function fallbackSanitize(string $content): string
    {
        $content = preg_replace('#<(script|style|iframe|object|embed|svg|math)\b[^>]*>.*?</\1>#is', '', $content) ?? '';
        $content = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $content) ?? '';
        $content = preg_replace('/href\s*=\s*("|\')\s*(javascript|data|vbscript):.*?\1/i', '', $content) ?? '';
        $content = preg_replace('/src\s*=\s*("|\')\s*(?!\/storage\/article-content\/|https?:\/\/[^"\']+\/storage\/article-content\/).*?\1/i', '', $content) ?? '';
        $content = strip_tags($content, '<'.implode('><', self::ALLOWED_TAGS).'>');

        return trim($content);
    }

    private static function looksLikeHtml(string $content): bool
    {
        return (bool) preg_match('/<\s*[a-zA-Z][^>]*>/', $content);
    }

    private static function containsArabic(string $value): bool
    {
        return (bool) preg_match('/\p{Arabic}/u', $value);
    }
}
