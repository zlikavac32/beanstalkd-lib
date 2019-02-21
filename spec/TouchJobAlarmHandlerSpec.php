<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib;

use Exception;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\BeanstalkdLib\Client;
use Zlikavac32\BeanstalkdLib\InterruptException;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\JobStats;
use Zlikavac32\BeanstalkdLib\TouchJobAlarmHandler;

class TouchJobAlarmHandlerSpec extends ObjectBehavior {

    public function let(Client $client): void {
        $this->beConstructedWith($client, 32);
    }

    public function it_is_initializable(): void {
        $this->shouldHaveType(TouchJobAlarmHandler::class);
    }

    public function it_should_schedule_if_enough_time_is_left(AlarmScheduler $scheduler): void {
        $scheduler->schedule(2, $this)
            ->shouldBeCalled();

        $this->scheduled($scheduler, 4)
            ->shouldReturn(true);
    }

    public function it_should_not_schedule_if_not_enough_time_is_left(AlarmScheduler $scheduler): void {
        $scheduler->schedule(Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->scheduled($scheduler, 2)
            ->shouldReturn(false);
    }

    public function it_should_touch_and_reschedule_if_enough_time_is_left(
        AlarmScheduler $scheduler,
        Client $client,
        JobHandle $jobHandle,
        JobStats $jobStats
    ): void {
        $scheduler->schedule(2, $this)
            ->shouldBeCalled();

        $jobHandle->touch()->shouldBeCalled();

        $client->peek(32)->willReturn($jobHandle);

        $jobHandle->stats()->willReturn($jobStats);

        $jobStats->timeLeft()->willReturn(4);

        $this->handle($scheduler);
    }

    public function it_should_touch_and_not_reschedule_if_not_enough_time_is_left(
        AlarmScheduler $scheduler,
        Client $client,
        JobHandle $jobHandle,
        JobStats $jobStats
    ): void {
        $scheduler->schedule(2, $this)
            ->shouldNotBeCalled();

        $jobHandle->touch()->shouldBeCalled();

        $client->peek(32)->willReturn($jobHandle);

        $jobHandle->stats()->willReturn($jobStats);

        $jobStats->timeLeft()->willReturn(2);

        $this->handle($scheduler);
    }

    public function it_should_rethrow_interrupt_exception(AlarmScheduler $scheduler, Client $client): void {
        $e = new InterruptException();

        $client->peek(32)->willThrow($e);

        $this->shouldThrow($e)->duringHandle($scheduler);
    }

    public function it_should_ignore_any_other_throwable(AlarmScheduler $scheduler, Client $client): void {
        $client->peek(32)->willThrow(new Exception());

        $this->handle($scheduler);
    }
}
