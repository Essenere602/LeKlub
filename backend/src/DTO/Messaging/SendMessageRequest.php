<?php

declare(strict_types=1);

namespace App\DTO\Messaging;

use Symfony\Component\Validator\Constraints as Assert;

final class SendMessageRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 1000)]
    public string $content = '';

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->content = trim(strip_tags((string) ($data['content'] ?? '')));

        return $request;
    }
}
