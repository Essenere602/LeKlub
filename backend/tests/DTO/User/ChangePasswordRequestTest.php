<?php

declare(strict_types=1);

namespace App\Tests\DTO\User;

use App\DTO\User\ChangePasswordRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ChangePasswordRequestTest extends TestCase
{
    public function testItAcceptsValidPasswordChangePayload(): void
    {
        $request = ChangePasswordRequest::fromArray([
            'currentPassword' => 'OldPassword123',
            'newPassword' => 'NewPassword123',
            'newPasswordConfirmation' => 'NewPassword123',
        ]);

        $violations = self::validator()->validate($request);

        self::assertCount(0, $violations);
    }

    public function testItRejectsWeakPasswordAndInvalidConfirmation(): void
    {
        $request = ChangePasswordRequest::fromArray([
            'currentPassword' => 'OldPassword123',
            'newPassword' => 'weak',
            'newPasswordConfirmation' => 'different',
        ]);

        $violations = self::validator()->validate($request);

        self::assertGreaterThanOrEqual(2, count($violations));
    }

    private static function validator(): \Symfony\Component\Validator\Validator\ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }
}
