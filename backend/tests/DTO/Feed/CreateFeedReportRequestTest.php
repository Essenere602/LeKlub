<?php

declare(strict_types=1);

namespace App\Tests\DTO\Feed;

use App\DTO\Feed\CreateFeedReportRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class CreateFeedReportRequestTest extends TestCase
{
    public function testItAcceptsValidReasonAndNormalizesDetails(): void
    {
        $dto = CreateFeedReportRequest::fromArray([
            'reason' => 'harassment',
            'details' => ' <strong>Repeated messages</strong> ',
        ]);

        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $violations = $validator->validate($dto);

        self::assertCount(0, $violations);
        self::assertSame('Repeated messages', $dto->details);
        self::assertSame('harassment', $dto->reasonEnum()->value);
    }

    public function testItRejectsUnknownReasonAndTooLongDetails(): void
    {
        $dto = CreateFeedReportRequest::fromArray([
            'reason' => 'unknown',
            'details' => str_repeat('a', 501),
        ]);

        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $violations = $validator->validate($dto);

        self::assertGreaterThanOrEqual(2, count($violations));
    }

    public function testItStoresEmptyDetailsAsNull(): void
    {
        $dto = CreateFeedReportRequest::fromArray([
            'reason' => 'other',
            'details' => '   ',
        ]);

        self::assertNull($dto->details);
    }
}
