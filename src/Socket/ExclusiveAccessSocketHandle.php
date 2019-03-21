<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Socket;

use Zlikavac32\BeanstalkdLib\SocketException;
use Zlikavac32\BeanstalkdLib\SocketHandle;

/**
 * Socket handle safe to be used in signal handlers. It offers
 * exclusive lock to the socket resource. If other thread tries to use
 * socket while it's being used, an exception will be thrown.
 *
 * Thrown code is SOCKET_EUSERS.
 */
class ExclusiveAccessSocketHandle implements SocketHandle
{

    /**
     * @var SocketHandle
     */
    private $socketHandle;

    private $inProgress = false;

    public function __construct(SocketHandle $socketHandle)
    {
        $this->socketHandle = $socketHandle;
    }

    public function write(string $buffer): void
    {
        $this->assertNotInProgress();

        try {
            $this->inProgress = true;
            $this->socketHandle->write($buffer);
        } finally {
            $this->inProgress = false;
        }
    }

    public function read(int $len, bool $interruptible = false): string
    {
        $this->assertNotInProgress();

        try {
            $this->inProgress = true;

            return $this->socketHandle->read($len, $interruptible);
        } finally {
            $this->inProgress = false;
        }
    }

    public function readLine(int $minimumLength = 0, bool $interruptible = false): string
    {
        $this->assertNotInProgress();

        try {
            $this->inProgress = true;

            return $this->socketHandle->readLine($minimumLength, $interruptible);
        } finally {
            $this->inProgress = false;
        }
    }

    public function close(): void
    {
        $this->assertNotInProgress();

        try {
            $this->inProgress = true;
            $this->socketHandle->close();
        } finally {
            $this->inProgress = false;
        }
    }

    private function assertNotInProgress(): void
    {
        if ($this->inProgress) {
            throw new SocketException(SOCKET_EUSERS);
        }
    }
}
