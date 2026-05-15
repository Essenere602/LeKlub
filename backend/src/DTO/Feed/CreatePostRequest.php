<?php

declare(strict_types=1);

namespace App\DTO\Feed;

use Symfony\Component\Validator\Constraints as Assert;

final class CreatePostRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 1000)]
    public string $content = '';

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->content = self::normalizeContent($data['content'] ?? '');

        return $request;
    }

    private static function normalizeContent(mixed $content): string
    {
        return trim(strip_tags((string) $content));
    }
}
