<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Exception;
use Throwable;

class InterruptException extends Exception
{

    public function __construct(Throwable $previous = null)
    {
        parent::__construct('Interrupt received', 0, $previous);
    }
}
