<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Client;

use Ds\Map;
use Ds\Set;
use Ds\Vector;
use LogicException;
use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\Client\ProtocolClient;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeConfiguration;
use Zlikavac32\BeanstalkdLib\Command;
use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\ProtocolTubePurger;
use Zlikavac32\BeanstalkdLib\Serializer;
use function Zlikavac32\BeanstalkdLib\TestHelper\phpSpec\beJobHandleFor;
use function Zlikavac32\BeanstalkdLib\TestHelper\phpSpec\beMapOfTubes;
use function Zlikavac32\BeanstalkdLib\TestHelper\phpSpec\beTubeHandleFor;

class ProtocolClientSpec extends ObjectBehavior
{
    public function let(Protocol $protocol, ProtocolTubePurger $protocolTubePurger): void
    {
        $this->beConstructedWith($protocol, $protocolTubePurger, new Map());
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProtocolClient::class);
    }

    public function it_should_list_known_tubes(
        Protocol $protocol,
        ProtocolTubePurger $protocolTubePurger,
        TubeConfiguration $fooTubeConfiguration,
        TubeConfiguration $barTubeConfiguration
    ): void {
        $this->beConstructedWith($protocol, $protocolTubePurger, new Map([
            'foo' => $fooTubeConfiguration->getWrappedObject(),
            'bar' => $barTubeConfiguration->getWrappedObject()
        ]));

        $tubes = ['foo', 'bar', 'baz'];

        $protocol->listTubes()
            ->willReturn(new Vector($tubes));

        $this->tubes()
            ->shouldBeMapOfTubes(new Set(['foo', 'bar']));
    }

    public function it_should_create_new_tube_handle(
        Protocol $protocol,
        ProtocolTubePurger $protocolTubePurger,
        TubeConfiguration $tubeConfiguration
    ): void {
        $this->beConstructedWith($protocol, $protocolTubePurger, new Map([
            'foo' => $tubeConfiguration->getWrappedObject()
        ]));

        $this->tube('foo')
            ->shouldBeTubeHandleFor('foo');
    }

    public function it_should_throw_exception_for_unknown_tube(
        Protocol $protocol,
        ProtocolTubePurger $protocolTubePurger
    ): void {
        $this->beConstructedWith($protocol, $protocolTubePurger, new Map());

        $this->shouldThrow(new LogicException('Tube configuration for tube foo not found'))
            ->duringTube('foo');
    }

    public function it_should_return_server_stats(Protocol $protocol): void
    {
        $protocol->stats()
            ->willReturn(
                [
                    'hostname'               => 'foo',
                    'version'                => 123.23,
                    'pid'                    => 123,
                    'uptime'                 => 324,
                    'max-job-size'           => 1,
                    'rusage-utime'           => 2.2,
                    'rusage-stime'           => 3.2,
                    'current-jobs-urgent'    => 4,
                    'current-jobs-ready'     => 5,
                    'current-jobs-reserved'  => 6,
                    'current-jobs-delayed'   => 7,
                    'current-jobs-buried'    => 8,
                    'current-tubes'          => 9,
                    'current-connections'    => 10,
                    'current-producers'      => 11,
                    'current-workers'        => 12,
                    'current-waiting'        => 13,
                    'job-timeouts'           => 14,
                    'total-jobs'             => 15,
                    'total-connections'      => 16,
                    'cmd-put'                => Command::PUT()
                        ->ordinal(),
                    'cmd-peek'               => Command::PEEK()
                        ->ordinal(),
                    'cmd-peek-ready'         => Command::PEEK_READY()
                        ->ordinal(),
                    'cmd-peek-delayed'       => Command::PEEK_DELAYED()
                        ->ordinal(),
                    'cmd-peek-buried'        => Command::PEEK_BURIED()
                        ->ordinal(),
                    'cmd-reserve'            => Command::RESERVE()
                        ->ordinal(),
                    'cmd-use'                => Command::USE()
                        ->ordinal(),
                    'cmd-watch'              => Command::WATCH()
                        ->ordinal(),
                    'cmd-ignore'             => Command::IGNORE()
                        ->ordinal(),
                    'cmd-delete'             => Command::DELETE()
                        ->ordinal(),
                    'cmd-release'            => Command::RELEASE()
                        ->ordinal(),
                    'cmd-bury'               => Command::BURY()
                        ->ordinal(),
                    'cmd-kick'               => Command::KICK()
                        ->ordinal(),
                    'cmd-stats'              => Command::STATS()
                        ->ordinal(),
                    'cmd-stats-job'          => Command::STATS_JOB()
                        ->ordinal(),
                    'cmd-stats-tube'         => Command::STATS_TUBE()
                        ->ordinal(),
                    'cmd-list-tubes'         => Command::LIST_TUBES()
                        ->ordinal(),
                    'cmd-list-tube-used'     => Command::LIST_TUBE_USED()
                        ->ordinal(),
                    'cmd-list-tubes-watched' => Command::LIST_TUBES_WATCHED()
                        ->ordinal(),
                    'cmd-pause-tube'         => Command::PAUSE_TUBE()
                        ->ordinal(),

                ]
            );

        $serverStats = $this->stats();

        $serverStats->hostname()
            ->shouldReturn('foo');
        $serverStats->version()
            ->shouldReturn('123.23');
        $serverStats->processId()
            ->shouldReturn(123);
        $serverStats->upTime()
            ->shouldReturn(324);
        $serverStats->maxJobSize()
            ->shouldReturn(1);
        $serverStats->cpuUserTime()
            ->shouldReturn(2.2);
        $serverStats->cpuSystemTime()
            ->shouldReturn(3.2);

        $serverMetrics = $serverStats->serverMetrics();

        $serverMetrics->numberOfUrgentJobs()
            ->shouldReturn(4);
        $serverMetrics->numberOfReadyJobs()
            ->shouldReturn(5);
        $serverMetrics->numberOfReservedJobs()
            ->shouldReturn(6);
        $serverMetrics->numberOfDelayedJobs()
            ->shouldReturn(7);
        $serverMetrics->numberOfBuriedJobs()
            ->shouldReturn(8);
        $serverMetrics->numberOfTubes()
            ->shouldReturn(9);
        $serverMetrics->numberOfConnections()
            ->shouldReturn(10);
        $serverMetrics->numberOfProduces()
            ->shouldReturn(11);
        $serverMetrics->numberOfWorkers()
            ->shouldReturn(12);
        $serverMetrics->numberOfClientsWaiting()
            ->shouldReturn(13);
        $serverMetrics->cumulativeNumberOfTimedOutJobs()
            ->shouldReturn(14);
        $serverMetrics->cumulativeNumberOfJobs()
            ->shouldReturn(15);
        $serverMetrics->cumulativeNumberOfConnections()
            ->shouldReturn(16);

        $commandMetrics = $serverStats->commandMetrics();

        foreach (Command::values() as $command) {
            $commandMetrics->numberOf($command)
                ->shouldReturn($command->ordinal());
        }
    }

    public function it_should_reserve_job(
        Protocol $protocol,
        ProtocolTubePurger $protocolTubePurger,
        TubeConfiguration $tubeConfiguration,
        Serializer $serializer
    ): void {
        $this->beConstructedWith($protocol, $protocolTubePurger, new Map([
            'foo' => $tubeConfiguration->getWrappedObject()
        ]));

        $jobPayload = '[1, 2]';

        $job = new Job(32, $jobPayload);

        $protocol->reserve()
            ->willReturn($job);

        $protocol->statsJob(32)
            ->willReturn(['tube' => 'foo']);

        $deserializedPayload = [1, 2];

        $serializer->deserialize($jobPayload)
            ->willReturn($deserializedPayload);

        $tubeConfiguration->serializer()
            ->willReturn($serializer);

        $this->reserve()
            ->shouldBeJobHandleFor(32, $deserializedPayload);
    }

    public function it_should_peek_job(
        Protocol $protocol,
        ProtocolTubePurger $protocolTubePurger,
        TubeConfiguration $tubeConfiguration,
        Serializer $serializer
    ): void {
        $this->beConstructedWith($protocol, $protocolTubePurger, new Map([
            'foo' => $tubeConfiguration->getWrappedObject()
        ]));

        $jobPayload = '[1, 2]';

        $job = new Job(32, $jobPayload);

        $protocol->peek(32)
            ->willReturn($job);

        $protocol->statsJob(32)
            ->willReturn(['tube' => 'foo']);

        $deserializedPayload = [1, 2];

        $serializer->deserialize($jobPayload)
            ->willReturn($deserializedPayload);

        $tubeConfiguration->serializer()
            ->willReturn($serializer);

        $this->peek(32)
            ->shouldBeJobHandleFor(32, $deserializedPayload);
    }

    public function it_should_reserve_with_timeout(
        Protocol $protocol,
        ProtocolTubePurger $protocolTubePurger,
        TubeConfiguration $tubeConfiguration,
        Serializer $serializer
    ): void {
        $this->beConstructedWith($protocol, $protocolTubePurger, new Map([
            'foo' => $tubeConfiguration->getWrappedObject()
        ]));

        $jobPayload = '[1, 2]';

        $job = new Job(32, $jobPayload);

        $protocol->reserveWithTimeout(18)
            ->willReturn($job);

        $protocol->statsJob(32)
            ->willReturn(['tube' => 'foo']);

        $deserializedPayload = [1, 2];

        $serializer->deserialize($jobPayload)
            ->willReturn($deserializedPayload);

        $tubeConfiguration->serializer()
            ->willReturn($serializer);

        $this->reserveWithTimeout(18)
            ->shouldBeJobHandleFor(32, $deserializedPayload);
    }

    public function it_should_watch_tube(Protocol $protocol): void
    {
        $protocol->watch('foo')
            ->willReturn(2);

        $this->watch('foo')
            ->shouldReturn(2);
    }

    public function it_should_ignore_default_tube(Protocol $protocol): void
    {
        $protocol->ignore('default')
            ->willReturn(1);

        $this->ignoreDefaultTube()
            ->shouldReturn(1);
    }

    public function it_should_ignore_tube(Protocol $protocol): void
    {
        $protocol->ignore('foo')
            ->willReturn(2);

        $this->ignore('foo')
            ->shouldReturn(2);
    }

    public function it_should_return_list_of_watched_tubes(Protocol $protocol): void
    {
        $list = new Set();

        $protocol->listTubesWatched()
            ->willReturn($list);

        $this->watchedTubeNames()
            ->shouldReturn($list);
    }

    public function it_should_flush_tubes(
        Protocol $protocol,
        ProtocolTubePurger $protocolTubePurger,
        TubeConfiguration $tubeConfiguration
    ): void {
        $this->beConstructedWith($protocol, $protocolTubePurger, new Map([
            'foo' => $tubeConfiguration->getWrappedObject()
        ]));

        $tubes = ['foo'];

        $protocol->listTubes()
            ->willReturn(new Vector($tubes));

        $states = new Set();

        $protocolTubePurger->purge($protocol, new Vector(['foo']), $states)->shouldBeCalled();

        $this->flush($states);
    }

    public function getMatchers(): array
    {
        return [
            'beJobHandleFor'  => function ($subject, int $jobId, $payload): bool {
                return beJobHandleFor($subject, $jobId, $payload);
            },
            'beTubeHandleFor' => function ($subject, string $tubeName): bool {
                return beTubeHandleFor($subject, $tubeName);
            },
            'beMapOfTubes'    => function ($subject, Set $expectedTubes): bool {
                return beMapOfTubes($subject, $expectedTubes);
            },
        ];
    }
}
