<?php

declare(strict_types=1);

namespace App\Application\Feed;

final class FeedContentNormalizer
{
    public static function normalize(mixed $content): string
    {
        return trim(strip_tags((string) $content));
    }
}
