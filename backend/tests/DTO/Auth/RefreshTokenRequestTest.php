<?php

declare(strict_types=1);

namespace App\Tests\DTO\Auth;

use App\DTO\Auth\RefreshTokenRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class RefreshTokenRequestTest extends TestCase
{
    public function testItAcceptsValidRefreshToken(): void
    {
        $request = RefreshTokenRequest::fromArray(['refreshToken' => str_repeat('a', 64)]);

        $violations = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator()->validate($request);

        self::assertCount(0, $violations);
    }

    public function testItRejectsMissingRefreshToken(): void
    {
        $request = RefreshTokenRequest::fromArray([]);

        $violations = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator()->validate($request);

        self::assertGreaterThanOrEqual(1, count($violations));
    }
}
