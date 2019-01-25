<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use RuntimeException;
use Throwable;

class DeserializeException extends RuntimeException {

    /**
     * @var string
     */
    private $causingPayload;

    public function __construct(string $message, string $causingPayload, Throwable $previous = null) {
        parent::__construct($message, 0, $previous);
        $this->causingPayload = $causingPayload;
    }

    public function causingPayload(): string {
        return $this->causingPayload;
    }
}
