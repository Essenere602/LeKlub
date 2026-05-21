<?php

declare(strict_types=1);

namespace App\Tests\DTO\Admin;

use App\DTO\Admin\ResolveFeedReportRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ResolveFeedReportRequestTest extends TestCase
{
    public function testItAcceptsValidDecisionAndNormalizesAdminNote(): void
    {
        $dto = ResolveFeedReportRequest::fromArray([
            'decision' => 'content_removed',
            'adminNote' => ' <strong>Justified</strong> ',
        ]);

        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $violations = $validator->validate($dto);

        self::assertCount(0, $violations);
        self::assertSame('content_removed', $dto->decisionEnum()->value);
        self::assertSame('Justified', $dto->adminNote);
    }

    public function testItRejectsInvalidDecisionAndTooLongAdminNote(): void
    {
        $dto = ResolveFeedReportRequest::fromArray([
            'decision' => 'delete_everything',
            'adminNote' => str_repeat('a', 501),
        ]);

        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $violations = $validator->validate($dto);

        self::assertGreaterThanOrEqual(2, count($violations));
    }
}
