<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\InterruptHandler;

use PhpSpec\ObjectBehavior;
use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\BeanstalkdLib\InterruptHandler\TimeoutHardInterruptHandler;

class TimeoutHardInterruptHandlerSpec extends ObjectBehavior {

    public function let(AlarmScheduler $scheduler, AlarmHandler $alarmHandler): void {
        $this->beConstructedWith($scheduler, $alarmHandler, 5);
    }

    public function it_is_initializable(): void {
        $this->shouldHaveType(TimeoutHardInterruptHandler::class);
    }

    public function it_should_handle_interrupt(AlarmScheduler $scheduler, AlarmHandler $alarmHandler): void {
        $scheduler->schedule(5, $alarmHandler)
            ->shouldBeCalled();

        $this->handle();
    }
}
