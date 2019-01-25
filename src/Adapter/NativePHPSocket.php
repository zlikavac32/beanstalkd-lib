<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Adapter;

use Zlikavac32\BeanstalkdLib\Socket;
use Zlikavac32\BeanstalkdLib\SocketException;
use Zlikavac32\BeanstalkdLib\SocketHandle;

class NativePHPSocket implements Socket {

    /**
     * @var int
     */
    private $domain;
    /**
     * @var int
     */
    private $type;
    /**
     * @var int
     */
    private $protocol;
    /**
     * @var int
     */
    private $readTimeout;

    /**
     * @var int $readTimeout In microseconds
     */
    public function __construct(
        int $readTimeout,
        int $domain = \AF_INET,
        int $type = \SOCK_STREAM,
        int $protocol = \SOL_TCP
    ) {
        $this->domain = $domain;
        $this->type = $type;
        $this->protocol = $protocol;
        $this->readTimeout = $readTimeout;
    }

    /**
     * @throws SocketException
     */
    public function open(string $ip, int $port): SocketHandle {
        $socket = \socket_create($this->domain, $this->type, $this->protocol);

        if (false === $socket) {
            throw new SocketException(\socket_last_error());
        }

        \socket_set_option(
            $socket
            ,
            \SOL_SOCKET,
            \SO_RCVTIMEO,
            [
                'sec'  => (int) $this->readTimeout / 1000000,
                'usec' => $this->readTimeout % 1000000,
            ]
        );
        \socket_set_option($socket, \SOL_TCP, \SO_KEEPALIVE, 1);
        \socket_set_block($socket);

        if (false === \socket_connect($socket, $ip, $port)) {
            throw new SocketException(socket_last_error($socket));
        }

        return new NativePHPSocketHandle($socket);
    }
}
