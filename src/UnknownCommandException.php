<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class UnknownCommandException extends ClientException {

    public function __construct() {
        parent::__construct('Unknown command was sent to the server', null);
    }
}
