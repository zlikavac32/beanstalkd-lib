<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\InterruptHandler;

use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\InterruptException;
use Zlikavac32\BeanstalkdLib\InterruptHandler\HardInterruptHandler;

class HardInterruptHandlerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(HardInterruptHandler::class);
    }

    public function it_should_do_nothing_by_default(): void {
        $this->handle();
    }

    public function it_should_throw_exception_on_second_handle(): void {
        $this->handle();
        $this->shouldThrow(new InterruptException())->duringHandle();
    }
}
