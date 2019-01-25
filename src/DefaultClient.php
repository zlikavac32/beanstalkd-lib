<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Ds\Map;
use Ds\Set;

class DefaultClient implements Client {

    /**
     * @var Protocol
     */
    private $protocol;
    /**
     * @var TubeConfiguration
     */
    private $tubeConfiguration;

    public function __construct(Protocol $protocol, TubeConfiguration $tubeConfiguration) {
        $this->protocol = $protocol;
        $this->tubeConfiguration = $tubeConfiguration;
    }

    /**
     * @inheritdoc
     */
    public function tubes(): Map {
        $allTubes = $this->protocol->listTubes();

        $map = new Map();

        foreach ($allTubes as $tubeName) {
            $map->put($tubeName, $this->tube($tubeName));
        }

        return $map;
    }

    /**
     * @inheritdoc
     */
    public function tube(string $tubeName): TubeHandle {
        return new DefaultTubeHandle(
            $tubeName,
            $this->protocol,
            $this->tubeConfiguration
        );
    }

    /**
     * @inheritdoc
     */
    public function stats(): ServerStats {
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
            (string) $stats['version'],
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
    public function reserve(): JobHandle {
        return $this->createJobHandleFromJob(
            $this->protocol->reserve()
        );
    }

    /**
     * @inheritdoc
     */
    public function peek(int $jobId): JobHandle {
        return $this->createJobHandleFromJob(
            $this->protocol->peek($jobId)
        );
    }

    /**
     * @inheritdoc
     */
    public function reserveWithTimeout(int $timeout): JobHandle {
        return $this->createJobHandleFromJob(
            $this->protocol->reserveWithTimeout($timeout)
        );
    }

    /**
     * @inheritdoc
     */
    public function watch(string $tubeName): int {
        return $this->protocol->watch($tubeName);
    }

    /**
     * @inheritdoc
     */
    public function ignoreDefaultTube(): int {
        return $this->protocol->ignore('default');
    }

    /**
     * @inheritdoc
     */
    public function ignore(string $tubeName): int {
        return $this->protocol->ignore($tubeName);
    }

    /**
     * @inheritdoc
     */
    public function watchedTubeNames(): Set {
        return $this->protocol->listTubesWatched();
    }

    private function createJobHandleFromJob(Job $job): JobHandle {
        return new DefaultJobHandle(
            $job->id(),
            $this->tubeConfiguration->serializer()
                ->deserialize($job->payload()),
            $this->protocol,
            $this->tubeConfiguration
        );
    }
}
