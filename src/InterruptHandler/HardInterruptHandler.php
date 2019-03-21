<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\InterruptHandler;

use Zlikavac32\BeanstalkdLib\InterruptException;
use Zlikavac32\BeanstalkdLib\InterruptHandler;

/**
 * Handler that upon second interrupt handling causes hard interrupt.
 */
class HardInterruptHandler implements InterruptHandler
{

    private $didHaveHandle = false;

    public function handle(): void
    {
        if ($this->didHaveHandle) {
            throw new InterruptException();
        }

        $this->didHaveHandle = true;
    }
}
