<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Client;

use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeConfiguration;
use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\TubeHandle;
use Zlikavac32\BeanstalkdLib\TubeMetrics;
use Zlikavac32\BeanstalkdLib\TubeStats;

class DefaultTubeHandle implements TubeHandle {

    /**
     * @var string
     */
    private $tubeName;
    /**
     * @var Protocol
     */
    private $protocol;
    /**
     * @var \Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeConfiguration
     */
    private $tubeConfiguration;

    public function __construct(string $tubeName, Protocol $protocol, TubeConfiguration $tubeConfiguration) {
        $this->tubeName = $tubeName;
        $this->protocol = $protocol;
        $this->tubeConfiguration = $tubeConfiguration;
    }

    public function tubeName(): string {
        return $this->tubeName;
    }

    /**
     * @inheritdoc
     */
    public function kick(int $numberOfJobs): int {
        $this->protocol->useTube($this->tubeName);

        return $this->protocol->kick($numberOfJobs);
    }

    /**
     * @inheritdoc
     */
    public function put($payload, ?int $priority = null, ?int $delay = null, ?int $timeToRun = null): JobHandle {
        $this->protocol->useTube($this->tubeName);

        return new DefaultJobHandle(
            $this->protocol->put(
                $priority ?? $this->tubeConfiguration->defaultPriority(),
                $delay ?? $this->tubeConfiguration->defaultDelay(),
                $timeToRun ?? $this->tubeConfiguration->defaultTimeToRun(),
                $this->tubeConfiguration->serializer()
                    ->serialize($payload)
            ),
            $payload,
            $this->protocol,
            $this->tubeConfiguration
        );
    }

    /**
     * @inheritdoc
     */
    public function stats(): TubeStats {
        $stats = $this->protocol->statsTube($this->tubeName);

        return new TubeStats(
            $stats['name'],
            $stats['pause'],
            $stats['pause-time-left'],
            new TubeMetrics(
                $stats['current-jobs-urgent'],
                $stats['current-jobs-ready'],
                $stats['current-jobs-reserved'],
                $stats['current-jobs-delayed'],
                $stats['current-jobs-buried'],
                $stats['total-jobs'],
                $stats['current-using'],
                $stats['current-waiting'],
                $stats['current-watching'],
                $stats['cmd-delete'],
                $stats['cmd-pause-tube']
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function pause(?int $delay = null): void {
        $this->protocol->useTube($this->tubeName);

        $this->protocol->pauseTube($this->tubeName, $delay ?? $this->tubeConfiguration->defaultTubePauseDelay());
    }

    /**
     * @inheritdoc
     */
    public function peekReady(): JobHandle {
        $this->protocol->useTube($this->tubeName);

        return $this->createJobHandleFromJob($this->protocol->peekReady());
    }

    /**
     * @inheritdoc
     */
    public function peekDelayed(): JobHandle {
        $this->protocol->useTube($this->tubeName);

        return $this->createJobHandleFromJob($this->protocol->peekDelayed());
    }

    /**
     * @inheritdoc
     */
    public function peekBuried(): JobHandle {
        $this->protocol->useTube($this->tubeName);

        return $this->createJobHandleFromJob($this->protocol->peekBuried());
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
