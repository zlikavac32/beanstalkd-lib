<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class InterruptedCallSocketException extends SocketException
{

    public function __construct()
    {
        parent::__construct(\SOCKET_EINTR);
    }
}
