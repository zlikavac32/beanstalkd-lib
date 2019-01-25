<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Throwable;

class InterruptException extends BeanstalkdLibException {

    public function __construct(Throwable $previous = null) {
        parent::__construct('Interrupt received', $previous);
    }
}
