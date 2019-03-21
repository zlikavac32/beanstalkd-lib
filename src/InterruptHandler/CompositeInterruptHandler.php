<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\InterruptHandler;

use Zlikavac32\BeanstalkdLib\InterruptHandler;

class CompositeInterruptHandler implements InterruptHandler
{

    /**
     * @var InterruptHandler[]
     */
    private $interruptHandlers;

    public function __construct(InterruptHandler ...$interruptHandlers)
    {
        $this->interruptHandlers = $interruptHandlers;
    }

    public function handle(): void
    {
        foreach ($this->interruptHandlers as $interruptHandler) {
            $interruptHandler->handle();
        }
    }
}
