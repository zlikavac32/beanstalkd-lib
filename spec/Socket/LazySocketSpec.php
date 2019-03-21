<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Socket;

use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\Socket;
use Zlikavac32\BeanstalkdLib\Socket\LazySocket;
use Zlikavac32\BeanstalkdLib\Socket\LazySocketHandle;

class LazySocketSpec extends ObjectBehavior
{

    public function let(Socket $socket): void
    {
        $this->beConstructedWith($socket);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(LazySocket::class);
    }

    public function it_should_create_lazy_socket_handle(Socket $socket): void
    {
        $this->open('127.0.0.1', 11300)
            ->shouldBeLike(new LazySocketHandle($socket->getWrappedObject(), '127.0.0.1', 11300));
    }
}
