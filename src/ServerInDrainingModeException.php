<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class ServerInDrainingModeException extends ClientException
{

    public function __construct()
    {
        parent::__construct('Server in draining mode', null);
    }
}
