<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib;

use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\ExclusiveAccessSocket;
use Zlikavac32\BeanstalkdLib\ExclusiveAccessSocketHandle;
use Zlikavac32\BeanstalkdLib\Socket;
use Zlikavac32\BeanstalkdLib\SocketHandle;

class ExclusiveAccessSocketSpec extends ObjectBehavior
{
    public function let(Socket $socket): void {
        $this->beConstructedWith($socket);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ExclusiveAccessSocket::class);
    }

    public function it_should_create_wrapped_handle(Socket $socket, SocketHandle $socketHandle): void {
        $socket->open('127.0.0.1', 11300)->willReturn($socketHandle);

        $this->open('127.0.0.1', 11300)
            ->shouldBeLike(new ExclusiveAccessSocketHandle(
                $socketHandle->getWrappedObject()
            ));
    }
}
