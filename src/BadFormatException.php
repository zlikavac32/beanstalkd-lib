<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class BadFormatException extends ClientException
{

    public function __construct()
    {
        parent::__construct('Message was sent in a bad format', null);
    }
}
