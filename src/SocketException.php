<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use RuntimeException;

class SocketException extends RuntimeException {

    public function __construct(int $code) {
        parent::__construct(\socket_strerror($code), $code);
    }
}
