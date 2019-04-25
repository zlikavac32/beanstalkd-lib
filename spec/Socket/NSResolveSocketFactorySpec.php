<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Socket;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Zlikavac32\BeanstalkdLib\Socket;
use Zlikavac32\BeanstalkdLib\Socket\NSResolveSocketFactory;
use Zlikavac32\BeanstalkdLib\SocketHandle;

class NSResolveSocketFactorySpec extends ObjectBehavior
{

    public function let(Socket $socket): void
    {
        $this->beConstructedWith($socket);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(NSResolveSocketFactory::class);
    }

    public function it_should_create_socket_with_ip(Socket $socket, SocketHandle $socketHandle): void
    {
        $socket->open('192.168.0.1', 11300)
            ->willReturn($socketHandle);

        $this->open('192.168.0.1', 11300)
            ->shouldReturn($socketHandle);
    }

    public function it_should_create_socket_with_hostname(Socket $socket, SocketHandle $socketHandle): void
    {
        $socket->open('127.0.0.1', 11300)
            ->willReturn($socketHandle);

        $this->open('localhost', 11300)
            ->shouldReturn($socketHandle);
    }
}
