<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Adapter\Symfony\Console;

use PhpSpec\ObjectBehavior;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\BeanstalkdLib\Adapter\Symfony\Console\ManageAlarmSchedulerEventListener;

class ManageAlarmSchedulerEventListenerSpec extends ObjectBehavior
{

    public function let(AlarmScheduler $alarmScheduler): void
    {
        $this->beConstructedWith($alarmScheduler);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ManageAlarmSchedulerEventListener::class);
    }

    public function it_should_start_scheduler_on_console_command(AlarmScheduler $alarmScheduler): void
    {
        $alarmScheduler->start()
            ->shouldBeCalled();

        $this->onConsoleCommand();
    }

    public function it_should_finish_on_console_terminate(AlarmScheduler $alarmScheduler): void
    {
        $alarmScheduler->finish()
            ->shouldBeCalled();

        $this->onConsoleTerminate();
    }
}
