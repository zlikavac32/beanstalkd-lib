<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Throwable;

class ServerException extends BeanstalkdLibException {

    /**
     * @var ServerErrorCause
     */
    private $cause;

    public function __construct(ServerErrorCause $cause, Throwable $previous = null) {
        parent::__construct(\sprintf('An server error occurred: %s', $cause), $previous);
        $this->cause = $cause;
    }

    public function cause(): ServerErrorCause {
        return $this->cause;
    }
}
