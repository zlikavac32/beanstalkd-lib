<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class DeadlineSoonException extends ClientException {

    public function __construct() {
        parent::__construct('Deadline soon', null);
    }
}
