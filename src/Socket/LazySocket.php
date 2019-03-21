<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Socket;

use Zlikavac32\BeanstalkdLib\Socket;
use Zlikavac32\BeanstalkdLib\SocketException;
use Zlikavac32\BeanstalkdLib\SocketHandle;

/**
 * Socket that is opened only when needed.
 */
class LazySocket implements Socket
{

    /**
     * @var Socket
     */
    private $socket;

    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * @throws SocketException
     */
    public function open(string $ip, int $port): SocketHandle
    {
        return new LazySocketHandle(
            $this->socket,
            $ip,
            $port
        );
    }
}
