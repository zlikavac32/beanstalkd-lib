<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Throwable;

class ReserveInterruptedException extends BeanstalkdLibException {

    public function __construct(Throwable $previous = null) {
        parent::__construct('Reserve interrupted', $previous);
    }
}
