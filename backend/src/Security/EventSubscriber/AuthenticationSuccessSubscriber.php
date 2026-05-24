<?php

declare(strict_types=1);

namespace App\Security\EventSubscriber;

use App\Application\Auth\RefreshTokenManager;
use App\Domain\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly RefreshTokenManager $refreshTokenManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $data = $event->getData();
        $data['refreshToken'] = $this->refreshTokenManager->createForUser($user);
        $event->setData($data);
    }
}
