<?php

declare(strict_types=1);

namespace App\DTO\Feed;

use App\Domain\ValueObject\FeedReportReason;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateFeedReportRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [self::class, 'allowedReasons'])]
    public string $reason = '';

    #[Assert\Length(max: 500)]
    public ?string $details = null;

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->reason = is_string($data['reason'] ?? null) ? trim($data['reason']) : '';

        $details = is_string($data['details'] ?? null) ? trim(strip_tags($data['details'])) : null;
        $request->details = $details === '' ? null : $details;

        return $request;
    }

    /**
     * @return list<string>
     */
    public static function allowedReasons(): array
    {
        return array_map(
            static fn (FeedReportReason $reason): string => $reason->value,
            FeedReportReason::cases()
        );
    }

    public function reasonEnum(): FeedReportReason
    {
        return FeedReportReason::from($this->reason);
    }
}
