<?php

declare(strict_types=1);

namespace App\Security\EventSubscriber;

use App\Shared\Api\ApiResponse;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class LoginRateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(service: 'limiter.login')]
        private readonly RateLimiterFactory $loginLimiter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->getPathInfo() !== '/api/auth/login' || $request->getMethod() !== 'POST') {
            return;
        }

        $rateLimit = $this->loginLimiter
            ->create(($request->getClientIp() ?? 'unknown').'|'.$this->loginIdentifier($request->getContent()))
            ->consume();

        if (!$rateLimit->isAccepted()) {
            $event->setResponse(ApiResponse::error('Too many attempts. Please try again later.', [], 429));
        }
    }

    private function loginIdentifier(string $content): string
    {
        $payload = json_decode($content, true);

        if (!is_array($payload)) {
            return 'unknown';
        }

        return strtolower(trim((string) ($payload['email'] ?? 'unknown')));
    }
}
