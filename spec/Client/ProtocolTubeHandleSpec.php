<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Client;

use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\Client\ProtocolJobHandle;
use Zlikavac32\BeanstalkdLib\Client\ProtocolTubeHandle;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeConfiguration;
use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\Serializer;
use function Zlikavac32\BeanstalkdLib\TestHelper\phpSpec\beJobHandleFor;

class ProtocolTubeHandleSpec extends ObjectBehavior
{

    public function let(Protocol $protocol, TubeConfiguration $tubeConfiguration): void
    {
        $this->beConstructedWith('foo', $protocol, $tubeConfiguration);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProtocolTubeHandle::class);
    }

    public function it_should_have_valid_tube_name(): void
    {
        $this->tubeName()
            ->shouldReturn('foo');
    }

    public function it_should_kick_n_jobs(Protocol $protocol): void
    {
        $protocol->useTube('foo')
            ->shouldBeCalled();
        $protocol->kick(8)
            ->willReturn(4);

        $this->kick(8)
            ->shouldReturn(4);
    }

    public function it_should_put_job_with_tube_configuration_defaults(
        Protocol $protocol,
        TubeConfiguration $tubeConfiguration,
        Serializer $serializer
    ): void {
        $protocol->useTube('foo')
            ->shouldBeCalled();

        $tubeConfiguration->defaultPriority()
            ->willReturn(1);
        $tubeConfiguration->defaultDelay()
            ->willReturn(2);
        $tubeConfiguration->defaultTimeToRun()
            ->willReturn(3);

        $tubeConfiguration->serializer()
            ->willReturn($serializer);

        $serializer->serialize([1, 2])
            ->willReturn('[1, 2]');

        $protocol->put(1, 2, 3, '[1, 2]')
            ->willReturn(32);

        $this->put([1, 2])
            ->shouldBeLike(
                new ProtocolJobHandle(32, [1, 2], $protocol->getWrappedObject(), $tubeConfiguration->getWrappedObject())
            );
    }

    public function it_should_put_job_with_custom_job_options(
        Protocol $protocol,
        TubeConfiguration $tubeConfiguration,
        Serializer $serializer
    ): void {
        $protocol->useTube('foo')
            ->shouldBeCalled();

        $tubeConfiguration->serializer()
            ->willReturn($serializer);

        $serializer->serialize([1, 2])
            ->willReturn('[1, 2]');

        $protocol->put(1, 2, 3, '[1, 2]')
            ->willReturn(32);

        $this->put([1, 2], 1, 2, 3)
            ->shouldBeLike(
                new ProtocolJobHandle(32, [1, 2], $protocol->getWrappedObject(), $tubeConfiguration->getWrappedObject())
            );
    }

    public function it_should_return_tube_stats(Protocol $protocol): void
    {
        $protocol->statsTube('foo')
            ->willReturn(
                [
                    'name'                  => 'foo',
                    'pause'                 => 1,
                    'pause-time-left'       => 2,
                    'current-jobs-urgent'   => 3,
                    'current-jobs-ready'    => 4,
                    'current-jobs-reserved' => 5,
                    'current-jobs-delayed'  => 6,
                    'current-jobs-buried'   => 7,
                    'total-jobs'            => 8,
                    'current-using'         => 9,
                    'current-waiting'       => 10,
                    'current-watching'      => 11,
                    'cmd-delete'            => 12,
                    'cmd-pause-tube'        => 13,
                ]
            );

        $tubeStats = $this->stats();

        $tubeStats->tubeName()
            ->shouldReturn('foo');
        $tubeStats->pauseDuration()
            ->shouldReturn(1);
        $tubeStats->remainingPauseTime()
            ->shouldReturn(2);

        $tubeMetrics = $tubeStats->metrics();

        $tubeMetrics->numberOfUrgentJobs()
            ->shouldReturn(3);
        $tubeMetrics->numberOfReadyJobs()
            ->shouldReturn(4);
        $tubeMetrics->numberOfReservedJobs()
            ->shouldReturn(5);
        $tubeMetrics->numberOfDelayedJobs()
            ->shouldReturn(6);
        $tubeMetrics->numberOfBuriedJobs()
            ->shouldReturn(7);
        $tubeMetrics->cumulativeNumberOfJobs()
            ->shouldReturn(8);
        $tubeMetrics->numberOfClientsUsing()
            ->shouldReturn(9);
        $tubeMetrics->numberOfClientsWaiting()
            ->shouldReturn(10);
        $tubeMetrics->numberOfClientsWatching()
            ->shouldReturn(11);
        $tubeMetrics->numberOfDeleteCommands()
            ->shouldReturn(12);
        $tubeMetrics->numberOfPauseTubeCommands()
            ->shouldReturn(13);
    }

    public function it_should_pause_tube_with_tube_configuration_default_delay(
        Protocol $protocol,
        TubeConfiguration $tubeConfiguration
    ): void {
        $tubeConfiguration->defaultTubePauseDelay()
            ->willReturn(13);

        $protocol->useTube('foo')
            ->shouldBeCalled();

        $protocol->pauseTube('foo', 13)
            ->shouldBeCalled();

        $this->pause();
    }

    public function it_should_pause_tube_with_custom_delay(Protocol $protocol): void
    {
        $protocol->useTube('foo')
            ->shouldBeCalled();

        $protocol->pauseTube('foo', 13)
            ->shouldBeCalled();

        $this->pause(13);
    }

    public function it_should_peek_ready_job(
        Protocol $protocol,
        TubeConfiguration $tubeConfiguration,
        Serializer $serializer
    ): void {
        $protocol->useTube('foo')
            ->shouldBeCalled();

        $protocol->peekReady()
            ->willReturn(new Job(32, '[1, 2]'));

        $serializer->deserialize('[1, 2]')
            ->willReturn([1, 2]);

        $tubeConfiguration->serializer()
            ->willReturn($serializer);

        $this->peekReady()
            ->shouldBeJobHandleFor(32, [1, 2]);
    }

    public function it_should_peek_dealyed_job(
        Protocol $protocol,
        TubeConfiguration $tubeConfiguration,
        Serializer $serializer
    ): void {
        $protocol->useTube('foo')
            ->shouldBeCalled();

        $protocol->peekDelayed()
            ->willReturn(new Job(32, '[1, 2]'));

        $serializer->deserialize('[1, 2]')
            ->willReturn([1, 2]);

        $tubeConfiguration->serializer()
            ->willReturn($serializer);

        $this->peekDelayed()
            ->shouldBeJobHandleFor(32, [1, 2]);
    }

    public function it_should_peek_buried_job(
        Protocol $protocol,
        TubeConfiguration $tubeConfiguration,
        Serializer $serializer
    ): void {
        $protocol->useTube('foo')
            ->shouldBeCalled();

        $protocol->peekBuried()
            ->willReturn(new Job(32, '[1, 2]'));

        $serializer->deserialize('[1, 2]')
            ->willReturn([1, 2]);

        $tubeConfiguration->serializer()
            ->willReturn($serializer);

        $this->peekBuried()
            ->shouldBeJobHandleFor(32, [1, 2]);
    }

    public function getMatchers(): array
    {
        return [
            'beJobHandleFor' => function ($subject, int $jobId, $payload): bool {
                return beJobHandleFor($subject, $jobId, $payload);
            },
        ];
    }
}
