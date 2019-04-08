<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Adapter\Symfony\Console;

use Ds\Map;
use Zlikavac32\BeanstalkdLib\InterruptHandler;

class SignalHandlerInstallerEventListener
{

    /**
     * @var InterruptHandler
     */
    private $interruptHandler;
    /**
     * @var Map
     */
    private $previousHandlers;

    public function __construct(InterruptHandler $interruptHandler)
    {
        $this->interruptHandler = $interruptHandler;
        $this->previousHandlers = new Map();
    }

    public function onConsoleCommand(): void
    {
        $signals = [SIGINT, SIGTERM, SIGQUIT];

        foreach ($signals as $signal) {
            $this->previousHandlers->put($signal, pcntl_signal_get_handler($signal));

            pcntl_signal($signal, function (): void {
                $this->interruptHandler->handle();
            });
        }
    }

    public function onConsoleTerminate(): void
    {
        foreach ($this->previousHandlers as $signal => $previousHandler) {
            pcntl_signal($signal, $previousHandler);
        }
    }
}
