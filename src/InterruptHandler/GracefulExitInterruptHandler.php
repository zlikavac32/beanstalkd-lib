<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\InterruptHandler;

use Zlikavac32\BeanstalkdLib\GracefulExit;
use Zlikavac32\BeanstalkdLib\InterruptHandler;

class GracefulExitInterruptHandler implements InterruptHandler, GracefulExit {

    private $inProgress = false;

    public function inProgress(): bool {
        return $this->inProgress;
    }

    public function handle(): void {
        $this->inProgress = true;
    }

    public function clear(): void {
        $this->inProgress = false;
    }
}
