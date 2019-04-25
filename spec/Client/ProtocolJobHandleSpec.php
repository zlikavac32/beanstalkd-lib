<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Client;

use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\Client\ProtocolJobHandle;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeConfiguration;
use Zlikavac32\BeanstalkdLib\JobState;
use Zlikavac32\BeanstalkdLib\Protocol;

class ProtocolJobHandleSpec extends ObjectBehavior
{

    public function let(Protocol $protocol, TubeConfiguration $tubeConfiguration): void
    {
        $this->beConstructedWith(32, [1, 2], $protocol, $tubeConfiguration);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProtocolJobHandle::class);
    }

    public function it_should_have_id(): void
    {
        $this->id()
            ->shouldReturn(32);
    }

    public function it_should_have_payload(): void
    {
        $this->payload()
            ->shouldReturn([1, 2]);
    }

    public function it_should_kick_job(Protocol $protocol): void
    {
        $protocol->kickJob(32)
            ->shouldBeCalled();

        $this->kick();
    }

    public function it_should_return_job_stats(Protocol $protocol): void
    {
        $protocol->statsJob(32)
            ->willReturn(
                [
                    'id'        => 32,
                    'tube'      => 'foo',
                    'state'     => 'ready',
                    'pri'       => 1024,
                    'age'       => 44,
                    'delay'     => 1,
                    'ttr'       => 22,
                    'time-left' => 3,
                    'reserves'  => 10,
                    'timeouts'  => 11,
                    'releases'  => 12,
                    'buries'    => 13,
                    'kicks'     => 14,
                ]
            );

        $jobStats = $this->stats();

        $jobStats->id()
            ->shouldReturn(32);
        $jobStats->tubeName()
            ->shouldReturn('foo');
        $jobStats->state()
            ->shouldReturn(JobState::READY());
        $jobStats->priority()
            ->shouldReturn(1024);
        $jobStats->age()
            ->shouldReturn(44);
        $jobStats->delay()
            ->shouldReturn(1);
        $jobStats->timeToRun()
            ->shouldReturn(22);
        $jobStats->timeLeft()
            ->shouldReturn(3);

        $jobMetrics = $jobStats->metrics();

        $jobMetrics->numberOfReserves()
            ->shouldReturn(10);
        $jobMetrics->numberOfTimeouts()
            ->shouldReturn(11);
        $jobMetrics->numberOfReleases()
            ->shouldReturn(12);
        $jobMetrics->numberOfBuries()
            ->shouldReturn(13);
        $jobMetrics->numberOfKicks()
            ->shouldReturn(14);
    }

    public function it_should_delete_job(Protocol $protocol): void
    {
        $protocol->delete(32)
            ->shouldBeCalled();

        $this->delete();
    }

    public function it_should_release_with_tube_defaults(Protocol $protocol, TubeConfiguration $tubeConfiguration): void
    {
        $tubeConfiguration->defaultPriority()
            ->willReturn(64);
        $tubeConfiguration->defaultDelay()
            ->willReturn(128);

        $protocol->release(32, 64, 128)
            ->shouldBeCalled();

        $this->release();
    }

    public function it_should_release_with_custom_delay_and_priority(Protocol $protocol): void
    {
        $protocol->release(32, 64, 128)
            ->shouldBeCalled();

        $this->release(64, 128);
    }

    public function it_should_bury_with_tube_defaults(Protocol $protocol, TubeConfiguration $tubeConfiguration): void
    {
        $tubeConfiguration->defaultPriority()
            ->willReturn(64);

        $protocol->bury(32, 64)
            ->shouldBeCalled();

        $this->bury();
    }

    public function it_should_bury_with_custom_priority(Protocol $protocol): void
    {
        $protocol->bury(32, 64)
            ->shouldBeCalled();

        $this->bury(64);
    }

    public function it_should_touch_job(Protocol $protocol): void
    {
        $protocol->touch(32)
            ->shouldBeCalled();

        $this->touch();
    }
}
