<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class ExpectedCRLFException extends ClientException
{

    public function __construct()
    {
        parent::__construct('Expected CRLF', null);
    }
}
