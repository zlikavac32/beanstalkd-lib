<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class JobStats
{

    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $tubeName;
    /**
     * @var JobState
     */
    private $state;
    /**
     * @var int
     */
    private $age;
    /**
     * @var int
     */
    private $delay;
    /**
     * @var int
     */
    private $timeToRun;
    /**
     * @var int
     */
    private $timeLeft;
    /**
     * @var JobMetrics
     */
    private $metrics;
    /**
     * @var int
     */
    private $priority;

    public function __construct(
        int $id,
        string $tubeName,
        JobState $state,
        int $priority,
        int $age,
        int $delay,
        int $timeToRun,
        int $timeLeft,
        JobMetrics $metrics
    ) {
        $this->id = $id;
        $this->tubeName = $tubeName;
        $this->state = $state;
        $this->age = $age;
        $this->delay = $delay;
        $this->timeToRun = $timeToRun;
        $this->timeLeft = $timeLeft;
        $this->metrics = $metrics;
        $this->priority = $priority;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function tubeName(): string
    {
        return $this->tubeName;
    }

    public function state(): JobState
    {
        return $this->state;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function age(): int
    {
        return $this->age;
    }

    public function delay(): int
    {
        return $this->delay;
    }

    public function timeToRun(): int
    {
        return $this->timeToRun;
    }

    public function timeLeft(): int
    {
        return $this->timeLeft;
    }

    public function metrics(): JobMetrics
    {
        return $this->metrics;
    }
}
