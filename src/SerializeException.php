<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use RuntimeException;
use Throwable;

class SerializeException extends RuntimeException {

    /**
     * @var mixed
     */
    private $causingPayload;

    /**
     * @param mixed $causingPayload
     */
    public function __construct(string $message, $causingPayload, Throwable $previous = null) {
        parent::__construct($message, 0, $previous);
        $this->causingPayload = $causingPayload;
    }

    /**
     * @return mixed
     */
    public function causingPayload() {
        return $this->causingPayload;
    }
}
