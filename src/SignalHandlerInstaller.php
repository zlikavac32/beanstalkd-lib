<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Ds\Map;

class SignalHandlerInstaller
{

    /**
     * @var InterruptHandler
     */
    private $interruptHandler;
    /**
     * @var Map
     */
    private $previousHandlers;

    private $previousAsyncSignals;

    public function __construct(InterruptHandler $interruptHandler)
    {
        $this->interruptHandler = $interruptHandler;
        $this->previousHandlers = new Map();
    }

    public function install(): void
    {
        $signals = [SIGINT, SIGTERM, SIGQUIT];

        foreach ($signals as $signal) {
            $this->previousHandlers->put($signal, pcntl_signal_get_handler($signal));

            pcntl_signal($signal, function (): void {
                $this->interruptHandler->handle();
            });
        }

        $this->previousAsyncSignals = pcntl_async_signals(true);
    }

    public function uninstall(): void
    {
        pcntl_async_signals($this->previousAsyncSignals);

        foreach ($this->previousHandlers as $signal => $previousHandler) {
            pcntl_signal($signal, $previousHandler);
        }
    }
}
