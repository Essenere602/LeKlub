<?php

declare(strict_types=1);

namespace App\DTO\Messaging;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateConversationRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $recipientId = 0;

    #[Assert\Length(max: 1000)]
    public ?string $firstMessage = null;

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->recipientId = (int) ($data['recipientId'] ?? 0);
        $message = trim(strip_tags((string) ($data['firstMessage'] ?? '')));
        $request->firstMessage = $message === '' ? null : $message;

        return $request;
    }
}
