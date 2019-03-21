<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use RuntimeException;
use Throwable;

class BeanstalkdLibException extends RuntimeException
{

    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
