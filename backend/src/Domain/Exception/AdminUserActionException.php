<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use RuntimeException;

final class AdminUserActionException extends RuntimeException
{
    public static function selfSuspensionForbidden(): self
    {
        return new self('An admin cannot suspend themself.');
    }

    public static function selfUnsuspensionForbidden(): self
    {
        return new self('An admin cannot unsuspend themself.');
    }

    public static function adminSuspensionForbidden(): self
    {
        return new self('Manual suspension of an admin user is not allowed.');
    }

    public static function selfRoleChangeForbidden(): self
    {
        return new self('An admin cannot modify their own roles.');
    }

    public static function suspendedUserPromotionForbidden(): self
    {
        return new self('A suspended user cannot be promoted to admin.');
    }

    public static function lastAdminDemotionForbidden(): self
    {
        return new self('The last admin cannot be demoted.');
    }
}
