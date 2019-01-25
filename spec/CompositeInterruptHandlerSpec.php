<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib;

use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\CompositeInterruptHandler;
use Zlikavac32\BeanstalkdLib\InterruptHandler;

class CompositeInterruptHandlerSpec extends ObjectBehavior {

    public function let(InterruptHandler $firstInterruptHandler, InterruptHandler $secondInterruptHandler) {
        $this->beConstructedWith($firstInterruptHandler, $secondInterruptHandler);
    }

    public function it_is_initializable(): void {
        $this->shouldHaveType(CompositeInterruptHandler::class);
    }

    public function it_should_delegate_calls(
        InterruptHandler $firstInterruptHandler,
        InterruptHandler $secondInterruptHandler
    ): void {
        $firstInterruptHandler->handle()
            ->shouldBeCalled();
        $secondInterruptHandler->handle()
            ->shouldBeCalled();

        $this->handle();
    }
}
