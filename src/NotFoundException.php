<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class NotFoundException extends ClientException {

    public function __construct($message = 'Not found received from the server') {
        parent::__construct($message, null);
    }
}
