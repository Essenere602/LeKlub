<?php

declare(strict_types=1);

namespace App\DTO\Feed;

use Symfony\Component\Validator\Constraints as Assert;

final class ReactToPostRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['like', 'dislike'])]
    public string $type = '';

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->type = trim((string) ($data['type'] ?? ''));

        return $request;
    }
}
