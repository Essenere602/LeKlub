<?php

declare(strict_types=1);

namespace App\Command;

use App\Infrastructure\WebSocket\ChatNotificationServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:websocket:server', description: 'Run LeKlub WebSocket notification server.')]
final class WebSocketServerCommand extends Command
{
    public function __construct(
        private readonly ChatNotificationServer $server,
        private readonly int $websocketPort = 8081,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $loop = Loop::get();
        $socket = new SocketServer('0.0.0.0:'.$this->websocketPort, [], $loop);

        new IoServer(
            new HttpServer(new WsServer($this->server)),
            $socket,
            $loop
        );

        $loop->addPeriodicTimer(1, fn (): null => $this->server->dispatchPendingNotifications());

        $output->writeln(sprintf('WebSocket server listening on ws://0.0.0.0:%d', $this->websocketPort));
        $loop->run();

        return Command::SUCCESS;
    }
}
