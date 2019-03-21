<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Socket;

use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\Socket;
use Zlikavac32\BeanstalkdLib\Socket\LazySocketHandle;
use Zlikavac32\BeanstalkdLib\SocketHandle;

class LazySocketHandleSpec extends ObjectBehavior {

    public function let(Socket $socket): void {
        $this->beConstructedWith($socket, '127.0.0.1', 11300);
    }

    public function it_is_initializable(): void {
        $this->shouldHaveType(LazySocketHandle::class);
    }

    public function it_should_open_on_write(Socket $socket, SocketHandle $socketHandle): void {
        $socket->open('127.0.0.1', 11300)
            ->willReturn($socketHandle);

        $socketHandle->write('foo')
            ->shouldBeCalled();

        $this->write('foo');
    }

    public function it_should_open_on_read_line(Socket $socket, SocketHandle $socketHandle): void {
        $socket->open('127.0.0.1', 11300)
            ->willReturn($socketHandle);

        $socketHandle->readLine(4, false)
            ->willReturn('foo');

        $this->readLine(4)
            ->shouldReturn('foo');
    }

    public function it_should_open_on_read(Socket $socket, SocketHandle $socketHandle): void {
        $socket->open('127.0.0.1', 11300)
            ->willReturn($socketHandle);

        $socketHandle->read(4, false)
            ->willReturn('foo');

        $this->read(4)
            ->shouldReturn('foo');
    }

    public function it_should_have_read_line_interruptible(Socket $socket, SocketHandle $socketHandle): void {
        $socket->open('127.0.0.1', 11300)
            ->willReturn($socketHandle);

        $socketHandle->readLine(4, true)
            ->willReturn('foo');

        $this->readLine(4, true)
            ->shouldReturn('foo');
    }

    public function it_should_have_read_interruptible(Socket $socket, SocketHandle $socketHandle): void {
        $socket->open('127.0.0.1', 11300)
            ->willReturn($socketHandle);

        $socketHandle->read(4, true)
            ->willReturn('foo');

        $this->read(4, true)
            ->shouldReturn('foo');
    }

    public function it_should_close(Socket $socket, SocketHandle $socketHandle): void {
        $socket->open('127.0.0.1', 11300)
            ->willReturn($socketHandle);

        $socketHandle->read(4, true)
            ->willReturn('foo');

        $socketHandle->close()
            ->shouldBeCalled();

        $this->read(4, true)
            ->shouldReturn('foo');

        $this->close();
    }
}
