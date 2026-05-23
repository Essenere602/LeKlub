<?php

declare(strict_types=1);

namespace App\Tests\DTO\Auth;

use App\DTO\Auth\ResetPasswordRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ResetPasswordRequestTest extends TestCase
{
    public function testItAcceptsValidResetPayload(): void
    {
        $request = ResetPasswordRequest::fromArray([
            'token' => str_repeat('a', 64),
            'newPassword' => 'NewPassword123',
            'newPasswordConfirmation' => 'NewPassword123',
        ]);

        $violations = self::validator()->validate($request);

        self::assertCount(0, $violations);
    }

    public function testItRejectsWeakPasswordAndInvalidConfirmation(): void
    {
        $request = ResetPasswordRequest::fromArray([
            'token' => 'short',
            'newPassword' => 'weak',
            'newPasswordConfirmation' => 'different',
        ]);

        $violations = self::validator()->validate($request);

        self::assertGreaterThanOrEqual(3, count($violations));
    }

    private static function validator(): \Symfony\Component\Validator\Validator\ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }
}
