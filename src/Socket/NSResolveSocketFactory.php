<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Socket;

use Zlikavac32\BeanstalkdLib\Socket;
use Zlikavac32\BeanstalkdLib\SocketHandle;

class NSResolveSocketFactory
{

    /**
     * @var Socket
     */
    private $socket;

    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }

    public function open(string $host, int $port): SocketHandle
    {
        return $this->socket->open(gethostbyname($host), $port);
    }
}
