<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Client;

use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeConfiguration;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\JobMetrics;
use Zlikavac32\BeanstalkdLib\JobState;
use Zlikavac32\BeanstalkdLib\JobStats;
use Zlikavac32\BeanstalkdLib\Protocol;

class DefaultJobHandle implements JobHandle
{

    /**
     * @var int
     */
    private $id;

    private $payload;
    /**
     * @var Protocol
     */
    private $protocol;
    /**
     * @var TubeConfiguration
     */
    private $tubeConfiguration;

    public function __construct(
        int $id,
        $payload,
        Protocol $protocol,
        TubeConfiguration $tubeConfiguration
    ) {
        $this->id = $id;
        $this->payload = $payload;
        $this->protocol = $protocol;
        $this->tubeConfiguration = $tubeConfiguration;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function payload()
    {
        return $this->payload;
    }

    /**
     * @inheritdoc
     */
    public function kick(): void
    {
        $this->protocol->kickJob($this->id);
    }

    /**
     * @inheritdoc
     */
    public function stats(): JobStats
    {
        $stats = $this->protocol->statsJob($this->id);

        return new JobStats(
            $stats['id'],
            $stats['tube'],
            JobState::valueOf(\strtoupper($stats['state'])),
            $stats['pri'],
            $stats['age'],
            $stats['delay'],
            $stats['ttr'],
            $stats['time-left'],
            new JobMetrics(
                $stats['reserves'],
                $stats['timeouts'],
                $stats['releases'],
                $stats['buries'],
                $stats['kicks']
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function delete(): void
    {
        $this->protocol->delete($this->id);
    }

    /**
     * @inheritdoc
     */
    public function release(?int $priority = null, ?int $delay = null): void
    {
        $this->protocol->release(
            $this->id,
            $priority ?? $this->tubeConfiguration->defaultPriority(),
            $delay ?? $this->tubeConfiguration->defaultDelay()
        );
    }

    /**
     * @inheritdoc
     */
    public function bury(?int $priority = null): void
    {
        $this->protocol->bury($this->id, $priority ?? $this->tubeConfiguration->defaultPriority());
    }

    /**
     * @inheritdoc
     */
    public function touch(): void
    {
        $this->protocol->touch($this->id);
    }
}
