<?php

declare(strict_types=1);

namespace App\Tests\DTO\Auth;

use App\DTO\Auth\ForgotPasswordRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ForgotPasswordRequestTest extends TestCase
{
    public function testItAcceptsValidEmail(): void
    {
        $request = ForgotPasswordRequest::fromArray(['email' => ' USER@EXAMPLE.TEST ']);

        $violations = self::validator()->validate($request);

        self::assertCount(0, $violations);
        self::assertSame('user@example.test', $request->email);
    }

    public function testItRejectsInvalidEmail(): void
    {
        $request = ForgotPasswordRequest::fromArray(['email' => 'invalid']);

        $violations = self::validator()->validate($request);

        self::assertGreaterThanOrEqual(1, count($violations));
    }

    private static function validator(): \Symfony\Component\Validator\Validator\ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }
}
