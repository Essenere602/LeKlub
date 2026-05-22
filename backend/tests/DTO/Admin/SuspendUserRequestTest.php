<?php

declare(strict_types=1);

namespace App\Tests\DTO\Admin;

use App\DTO\Admin\SuspendUserRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class SuspendUserRequestTest extends TestCase
{
    public function testItAcceptsValidSuspensionRequest(): void
    {
        $request = SuspendUserRequest::fromArray([
            'durationDays' => 7,
            'reason' => 'Comportement abusif',
        ]);

        self::assertCount(0, Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator()->validate($request));
        self::assertSame(7, $request->durationDays);
        self::assertSame('Comportement abusif', $request->reason);
    }

    public function testItRejectsInvalidDurationAndBlankReason(): void
    {
        $request = SuspendUserRequest::fromArray([
            'durationDays' => 3,
            'reason' => '   ',
        ]);

        $violations = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator()->validate($request);

        self::assertGreaterThanOrEqual(2, count($violations));
    }

    public function testItStripsHtmlFromReason(): void
    {
        $request = SuspendUserRequest::fromArray([
            'durationDays' => 1,
            'reason' => '<b>Spam</b>',
        ]);

        self::assertSame('Spam', $request->reason);
    }
}
