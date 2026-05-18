<?php

declare(strict_types=1);

namespace App\DTO\Feed;

use App\Application\Feed\FeedContentNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdatePostRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 1000)]
    public string $content = '';

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->content = FeedContentNormalizer::normalize($data['content'] ?? '');

        return $request;
    }
}
