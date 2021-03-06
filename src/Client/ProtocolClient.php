<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Client;

use Ds\Map;
use Ds\Set;
use LogicException;
use Zlikavac32\BeanstalkdLib\Client;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeConfiguration;
use Zlikavac32\BeanstalkdLib\Command;
use Zlikavac32\BeanstalkdLib\CommandMetrics;
use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\ProtocolTubePurger;
use Zlikavac32\BeanstalkdLib\ServerMetrics;
use Zlikavac32\BeanstalkdLib\ServerStats;
use Zlikavac32\BeanstalkdLib\TubeHandle;

class ProtocolClient implements Client
{

    /**
     * @var Protocol
     */
    private $protocol;
    /**
     * @var Map|TubeConfiguration[]
     */
    private $tubeConfigurations;
    /**
     * @var ProtocolTubePurger
     */
    private $protocolTubePurger;

    public function __construct(Protocol $protocol, ProtocolTubePurger $protocolTubePurger, Map $tubeConfigurations)
    {
        $this->protocol = $protocol;
        $this->tubeConfigurations = $tubeConfigurations;
        $this->protocolTubePurger = $protocolTubePurger;
    }

    /**
     * @inheritdoc
     */
    public function tubes(): Map
    {
        $allTubes = $this->protocol->listTubes();

        $map = new Map();

        foreach ($allTubes as $tubeName) {
            if (!$this->tubeConfigurations->hasKey($tubeName)) {
                continue;
            }

            $map->put($tubeName, $this->tube($tubeName));
        }

        return $map;
    }

    /**
     * @inheritdoc
     */
    public function tube(string $tubeName): TubeHandle
    {
        if (!$this->tubeConfigurations->hasKey($tubeName)) {
            throw new LogicException(sprintf('Tube configuration for tube %s not found', $tubeName));
        }

        return new ProtocolTubeHandle(
            $tubeName,
            $this->protocol,
            $this->protocolTubePurger,
            $this->tubeConfigurations->get($tubeName)
        );
    }

    /**
     * @inheritdoc
     */
    public function stats(): ServerStats
    {
        $stats = $this->protocol->stats();

        $commandMetricsMap = new Map();

        foreach (Command::values() as $command) {
            $expectedKey = 'cmd-' . \str_replace('_', '-', \strtolower($command->name()));

            $commandMetricsMap->put(
                $command,
                $stats[$expectedKey]
            );
        }

        return new ServerStats(
            $stats['hostname'],
            (string)$stats['version'],
            $stats['pid'],
            $stats['uptime'],
            $stats['max-job-size'],
            $stats['rusage-utime'],
            $stats['rusage-stime'],
            new ServerMetrics(
                $stats['current-jobs-urgent'],
                $stats['current-jobs-ready'],
                $stats['current-jobs-reserved'],
                $stats['current-jobs-delayed'],
                $stats['current-jobs-buried'],
                $stats['current-tubes'],
                $stats['current-connections'],
                $stats['current-producers'],
                $stats['current-workers'],
                $stats['current-waiting'],
                $stats['job-timeouts'],
                $stats['total-jobs'],
                $stats['total-connections']
            ),
            new CommandMetrics($commandMetricsMap)
        );
    }

    /**
     * @inheritdoc
     */
    public function reserve(): JobHandle
    {
        return $this->createJobHandleFromJob(
            $this->protocol->reserve()
        );
    }

    /**
     * @inheritdoc
     */
    public function peek(int $jobId): JobHandle
    {
        return $this->createJobHandleFromJob(
            $this->protocol->peek($jobId)
        );
    }

    /**
     * @inheritdoc
     */
    public function reserveWithTimeout(int $timeout): JobHandle
    {
        return $this->createJobHandleFromJob(
            $this->protocol->reserveWithTimeout($timeout)
        );
    }

    /**
     * @inheritdoc
     */
    public function watch(string $tubeName): int
    {
        return $this->protocol->watch($tubeName);
    }

    /**
     * @inheritdoc
     */
    public function ignoreDefaultTube(): int
    {
        return $this->protocol->ignore('default');
    }

    /**
     * @inheritdoc
     */
    public function ignore(string $tubeName): int
    {
        return $this->protocol->ignore($tubeName);
    }

    /**
     * @inheritdoc
     */
    public function watchedTubeNames(): Set
    {
        return $this->protocol->listTubesWatched();
    }

    private function createJobHandleFromJob(Job $job): JobHandle
    {
        $tubeName = $this->protocol->statsJob($job->id())['tube'];

        $tubeConfiguration = $this->tubeConfigurations->get($tubeName);
        assert($tubeConfiguration instanceof TubeConfiguration);

        return new ProtocolJobHandle(
            $job->id(),
            $tubeConfiguration->serializer()
                ->deserialize($job->payload()),
            $this->protocol,
            $tubeConfiguration
        );
    }

    /**
     * @inheritdoc
     */
    public function flush(Set $states): void
    {
        foreach ($this->tubes() as $tube) {
            assert($tube instanceof TubeHandle);

            $tube->flush($states);
        }
    }
}
