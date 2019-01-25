<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Adapter;

use LogicException;
use Zlikavac32\BeanstalkdLib\InterruptedCallSocketException;
use Zlikavac32\BeanstalkdLib\SocketException;
use Zlikavac32\BeanstalkdLib\SocketHandle;
use Zlikavac32\BeanstalkdLib\TryAgainSocketException;

class NativePHPSocketHandle implements SocketHandle {

    /**
     * @var resource
     */
    private $socket;

    public function __construct($socket) {
        if (!\is_resource($socket)) {
            throw new LogicException('Expected socket to be resource');
        }

        $this->socket = $socket;
    }

    /**
     * @throws SocketException
     */
    public function write(string $buffer): void {
        $this->ensureSocketNotClosed();

        $leftToWrite = \strlen($buffer);

        while (true) {
            $bytesWritten = @\socket_write($this->socket, $buffer, \strlen($buffer));

            if ($bytesWritten === false) {
                $lastError = \socket_last_error($this->socket);

                if (\SOCKET_EINTR === $lastError) {
                    continue;
                }

                throw $this->createExceptionForCurrentSocket($lastError);
            }

            if ($bytesWritten === $leftToWrite) {
                return;
            }

            $buffer = \substr($buffer, $bytesWritten);
            $leftToWrite -= $bytesWritten;
        }

        throw $this->createExceptionForCurrentSocket(\socket_last_error($this->socket));
    }

    /**
     * @throws SocketException
     */
    public function read(int $len, bool $interruptible = false): string {
        $this->ensureSocketNotClosed();

        $buffer = '';

        while (true) {
            if (false === @\socket_recv($this->socket, $readBuffer, $len, \MSG_WAITALL)) {
                $lastError = \socket_last_error($this->socket);

                if ($interruptible) {
                    throw $this->createExceptionForCurrentSocket($lastError);
                }

                if (\SOCKET_EINTR === $lastError || \SOCKET_EAGAIN === $lastError) {
                    continue;
                }

                break;
            }

            $buffer .= $readBuffer;
            $len -= \strlen($readBuffer);

            if (0 === $len) {
                return $buffer;
            }
        }

        throw $this->createExceptionForCurrentSocket(socket_last_error($this->socket));
    }

    public function readLine(int $minimumLength = 0, bool $interruptible = false): string {
        $minimumLength += 2;

        $line = $this->read($minimumLength, $interruptible);

        $expectedEnd = \strlen($line) - 2;

        while ($line[$expectedEnd] !== "\r" && $line[$expectedEnd + 1] !== "\n") {
            // since it's intended to be used with reserve, after initial line start, assume rest of the bytes will
            // come for sure
            $line .= $this->read(1);
            $expectedEnd++;
        }

        return \substr($line, 0, -2);
    }

    /**
     * @throws SocketException
     */
    public function close(): void {
        if (null === $this->socket) {
            return;
        }

        \socket_close($this->socket);

        $this->socket = null;
    }

    public function __destruct() {
        $this->close();
    }

    private function ensureSocketNotClosed(): void {
        if (null === $this->socket) {
            throw new LogicException('Socket closed');
        }
    }

    private function createExceptionForCurrentSocket(int $lastError): SocketException {
        switch ($lastError) {
            case 0:
                throw new LogicException('No error occurred. Why did you call this method?');
            case \SOCKET_EAGAIN:
                throw new TryAgainSocketException();
            case \SOCKET_EINTR:
                throw new InterruptedCallSocketException();
        }

        throw new SocketException($lastError);
    }
}
