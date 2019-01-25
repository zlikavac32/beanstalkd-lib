<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

/**
 * Socket safe to be used in signal handlers. It offers
 * exclusive lock to the socket resource. If other thread tries to use
 * socket while it's being used, an exception will be thrown.
 *
 * Thrown code is SOCKET_EUSERS.
 */
class ExclusiveAccessSocket implements Socket {

    /**
     * @var Socket
     */
    private $socket;

    public function __construct(Socket $socket) {
        $this->socket = $socket;
    }

    /**
     * @throws SocketException
     */
    public function open(string $ip, int $port): SocketHandle {
        return new ExclusiveAccessSocketHandle(
            $this->socket->open($ip, $port)
        );
    }
}
