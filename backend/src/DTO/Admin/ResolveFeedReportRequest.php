<?php

declare(strict_types=1);

namespace App\DTO\Admin;

use App\Domain\ValueObject\FeedReportDecision;
use Symfony\Component\Validator\Constraints as Assert;

final class ResolveFeedReportRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [self::class, 'allowedDecisions'])]
    public string $decision = '';

    #[Assert\Length(max: 500)]
    public ?string $adminNote = null;

    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->decision = is_string($data['decision'] ?? null) ? trim($data['decision']) : '';

        $adminNote = is_string($data['adminNote'] ?? null) ? trim(strip_tags($data['adminNote'])) : null;
        $request->adminNote = $adminNote === '' ? null : $adminNote;

        return $request;
    }

    /**
     * @return list<string>
     */
    public static function allowedDecisions(): array
    {
        return array_map(
            static fn (FeedReportDecision $decision): string => $decision->value,
            FeedReportDecision::cases()
        );
    }

    public function decisionEnum(): FeedReportDecision
    {
        return FeedReportDecision::from($this->decision);
    }
}
