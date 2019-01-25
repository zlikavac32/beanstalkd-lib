<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class ReserveTimedOutException extends ClientException {

    /**
     * @var int
     */
    private $usedTimeout;

    public function __construct(int $usedTimeout) {
        parent::__construct(\sprintf('Reserve timed out (%d seconds)', $usedTimeout), null);
        $this->usedTimeout = $usedTimeout;
    }

    public function usedTimeout(): int {
        return $this->usedTimeout;
    }
}
