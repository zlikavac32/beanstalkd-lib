<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Adapter\Symfony\Console;

use Zlikavac32\BeanstalkdLib\ClearableGracefulExit;
use Zlikavac32\BeanstalkdLib\GracefulExit;

class ClearGracefulExitEventListener
{

    /**
     * @var GracefulExit
     */
    private $gracefulExit;

    public function __construct(GracefulExit $gracefulExit)
    {
        $this->gracefulExit = $gracefulExit;
    }

    public function onConsoleTerminate(): void
    {
        if ($this->gracefulExit instanceof ClearableGracefulExit) {
            $this->gracefulExit->clear();
        }
    }
}
