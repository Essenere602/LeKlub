<?php

declare(strict_types=1);

namespace App\Infrastructure\WebSocket;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

final class ChatNotificationServer implements MessageComponentInterface
{
    private SplObjectStorage $connections;

    /**
     * @var array<int, list<ConnectionInterface>>
     */
    private array $connectionsByUserId = [];

    private int $lastNotificationOffset = 0;

    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly string $projectDir,
    ) {
        $this->connections = new SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->connections->attach($conn);
        $conn->send(json_encode(['type' => 'connected'], JSON_THROW_ON_ERROR));
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $payload = json_decode((string) $msg, true);

        if (!is_array($payload) || !isset($payload['type'])) {
            $this->sendError($from, 'Invalid message format.');
            return;
        }

        if ($payload['type'] === 'ping') {
            $from->send(json_encode(['type' => 'pong'], JSON_THROW_ON_ERROR));
            return;
        }

        if ($payload['type'] !== 'auth') {
            $this->sendError($from, 'Authentication required.');
            return;
        }

        $token = (string) ($payload['token'] ?? '');
        if ($token === '') {
            $this->sendError($from, 'Token is required.');
            return;
        }

        try {
            $decoded = $this->jwtManager->parse($token);
        } catch (\Throwable) {
            $this->sendError($from, 'Invalid token.');
            return;
        }

        $userId = (int) ($decoded['id'] ?? 0);
        if ($userId <= 0) {
            $this->sendError($from, 'Invalid token payload.');
            return;
        }

        $from->userId = $userId;
        $this->connectionsByUserId[$userId] ??= [];
        $this->connectionsByUserId[$userId][] = $from;

        $from->send(json_encode(['type' => 'authenticated', 'userId' => $userId], JSON_THROW_ON_ERROR));
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->connections->detach($conn);

        $userId = $conn->userId ?? null;
        if ($userId !== null && isset($this->connectionsByUserId[$userId])) {
            $this->connectionsByUserId[$userId] = array_values(array_filter(
                $this->connectionsByUserId[$userId],
                fn (ConnectionInterface $connection): bool => $connection !== $conn
            ));
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $conn->close();
    }

    public function dispatchPendingNotifications(): void
    {
        $file = $this->projectDir.'/var/websocket/notifications.jsonl';
        if (!is_file($file)) {
            return;
        }

        $handle = fopen($file, 'r');
        if ($handle === false) {
            return;
        }

        fseek($handle, $this->lastNotificationOffset);

        while (($line = fgets($handle)) !== false) {
            $this->lastNotificationOffset = ftell($handle);
            $payload = json_decode($line, true);

            if (!is_array($payload)) {
                continue;
            }

            $recipientId = (int) ($payload['recipientId'] ?? 0);
            foreach ($this->connectionsByUserId[$recipientId] ?? [] as $connection) {
                $connection->send(json_encode($payload, JSON_THROW_ON_ERROR));
            }
        }

        fclose($handle);
    }

    private function sendError(ConnectionInterface $connection, string $message): void
    {
        $connection->send(json_encode([
            'type' => 'error',
            'message' => $message,
        ], JSON_THROW_ON_ERROR));
    }
}
