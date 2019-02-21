<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib;

use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\GracefulExitInterruptHandler;

class GracefulExitInterruptHandlerSpec extends ObjectBehavior {

    public function it_is_initializable(): void {
        $this->shouldHaveType(GracefulExitInterruptHandler::class);
    }

    public function it_should_not_be_in_progress_by_default(): void {
        $this->inProgress()
            ->shouldReturn(false);
    }

    public function it_should_be_in_progress_after_interrupt(): void {
        $this->handle();
        $this->inProgress()
            ->shouldReturn(true);
    }

    public function it_should_not_be_in_progress_after_interrupt_and_clear(): void {
        $this->handle();
        $this->clear();

        $this->inProgress()->shouldReturn(false);
    }
}
