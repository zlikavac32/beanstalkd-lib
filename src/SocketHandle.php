<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

interface SocketHandle {

    /**
     * @throws SocketException
     */
    public function write(string $buffer): void;

    /**
     * @throws SocketException
     */
    public function read(int $len, bool $interruptible = false): string;

    public function readLine(int $minimumLength = 0, bool $interruptible = false): string;

    /**
     * @throws SocketException
     */
    public function close(): void;
}
