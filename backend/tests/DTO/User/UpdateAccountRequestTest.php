<?php

declare(strict_types=1);

namespace App\Tests\DTO\User;

use App\DTO\User\UpdateAccountRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class UpdateAccountRequestTest extends TestCase
{
    public function testItAcceptsValidAccountPayload(): void
    {
        $request = UpdateAccountRequest::fromArray([
            'email' => ' NEW.EMAIL@EXAMPLE.COM ',
            'username' => 'samuel_60',
            'currentPassword' => 'CurrentPassword123',
        ]);

        $violations = self::validator()->validate($request);

        self::assertCount(0, $violations);
        self::assertSame('new.email@example.com', $request->email);
        self::assertSame('samuel_60', $request->username);
    }

    public function testItRejectsInvalidEmailAndUsername(): void
    {
        $request = UpdateAccountRequest::fromArray([
            'email' => 'invalid-email',
            'username' => 'bad username',
        ]);

        $violations = self::validator()->validate($request);

        self::assertGreaterThanOrEqual(2, count($violations));
    }

    public function testItRejectsEmptyProvidedUsername(): void
    {
        $request = UpdateAccountRequest::fromArray([
            'username' => ' ',
        ]);

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
