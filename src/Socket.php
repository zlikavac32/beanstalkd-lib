<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

interface Socket
{

    /**
     * @throws SocketException
     */
    public function open(string $ip, int $port): SocketHandle;
}
