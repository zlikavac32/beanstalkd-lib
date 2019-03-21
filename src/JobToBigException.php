<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class JobToBigException extends ClientException
{

    public function __construct()
    {
        parent::__construct('Job to big', null);
    }
}
