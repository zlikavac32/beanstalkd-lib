<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Socket;

use Zlikavac32\BeanstalkdLib\Socket;
use Zlikavac32\BeanstalkdLib\SocketException;
use Zlikavac32\BeanstalkdLib\SocketHandle;

/**
 * Socket handle that is opened only when it's needed.
 */
class LazySocketHandle implements SocketHandle {

    /**
     * @var Socket
     */
    private $socket;
    /**
     * @var string
     */
    private $ip;
    /**
     * @var int
     */
    private $port;

    /**
     * @var SocketHandle
     */
    private $socketHandle;

    public function __construct(Socket $socket, string $ip, int $port) {
        $this->socket = $socket;
        $this->ip = $ip;
        $this->port = $port;
    }

    /**
     * @throws SocketException
     */
    public function write(string $buffer): void {
        $this->ensureSocketOpen();

        $this->socketHandle->write($buffer);
    }

    /**
     * @throws SocketException
     */
    public function read(int $len, bool $interruptible = false): string {
        $this->ensureSocketOpen();

        return $this->socketHandle->read($len, $interruptible);
    }

    public function readLine(int $minimumLength = 0, bool $interruptible = false): string {
        $this->ensureSocketOpen();

        return $this->socketHandle->readLine($minimumLength, $interruptible);
    }

    /**
     * @throws SocketException
     */
    public function close(): void {
        if (null === $this->socketHandle) {
            return ;
        }

        $this->socketHandle->close();
    }

    private function ensureSocketOpen(): void {
        if (null !== $this->socketHandle) {
            return ;
        }

        $this->socketHandle = $this->socket->open(
            $this->ip,
            $this->port
        );
    }
}
