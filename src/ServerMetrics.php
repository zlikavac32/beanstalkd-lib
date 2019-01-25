<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class ServerMetrics {

    /**
     * @var int
     */
    private $numberOfUrgentJobs;
    /**
     * @var int
     */
    private $numberOfReadyJobs;
    /**
     * @var int
     */
    private $numberOfReservedJobs;
    /**
     * @var int
     */
    private $numberOfDelayedJobs;
    /**
     * @var int
     */
    private $numberOfBuriedJobs;
    /**
     * @var int
     */
    private $numberOfTubes;
    /**
     * @var int
     */
    private $numberOfConnections;
    /**
     * @var int
     */
    private $numberOfProduces;
    /**
     * @var int
     */
    private $numberOfWorkers;
    /**
     * @var int
     */
    private $numberOfClientsWaiting;
    /**
     * @var int
     */
    private $cumulativeNumberOfTimedOutJobs;
    /**
     * @var int
     */
    private $cumulativeNumberOfJobs;
    /**
     * @var int
     */
    private $cumulativeNumberOfConnections;

    public function __construct(
        int $numberOfUrgentJobs,
        int $numberOfReadyJobs,
        int $numberOfReservedJobs,
        int $numberOfDelayedJobs,
        int $numberOfBuriedJobs,
        int $numberOfTubes,
        int $numberOfConnections,
        int $numberOfProduces,
        int $numberOfWorkers,
        int $numberOfClientsWaiting,
        int $cumulativeNumberOfTimedOutJobs,
        int $cumulativeNumberOfJobs,
        int $cumulativeNumberOfConnections
    ) {
        $this->numberOfUrgentJobs = $numberOfUrgentJobs;
        $this->numberOfReadyJobs = $numberOfReadyJobs;
        $this->numberOfReservedJobs = $numberOfReservedJobs;
        $this->numberOfDelayedJobs = $numberOfDelayedJobs;
        $this->numberOfBuriedJobs = $numberOfBuriedJobs;
        $this->numberOfTubes = $numberOfTubes;
        $this->numberOfConnections = $numberOfConnections;
        $this->numberOfProduces = $numberOfProduces;
        $this->numberOfWorkers = $numberOfWorkers;
        $this->numberOfClientsWaiting = $numberOfClientsWaiting;
        $this->cumulativeNumberOfTimedOutJobs = $cumulativeNumberOfTimedOutJobs;
        $this->cumulativeNumberOfJobs = $cumulativeNumberOfJobs;
        $this->cumulativeNumberOfConnections = $cumulativeNumberOfConnections;
    }

    public function numberOfUrgentJobs(): int {
        return $this->numberOfUrgentJobs;
    }

    public function numberOfReadyJobs(): int {
        return $this->numberOfReadyJobs;
    }

    public function numberOfReservedJobs(): int {
        return $this->numberOfReservedJobs;
    }

    public function numberOfDelayedJobs(): int {
        return $this->numberOfDelayedJobs;
    }

    public function numberOfBuriedJobs(): int {
        return $this->numberOfBuriedJobs;
    }

    public function numberOfTubes(): int {
        return $this->numberOfTubes;
    }

    public function numberOfConnections(): int {
        return $this->numberOfConnections;
    }

    public function numberOfProduces(): int {
        return $this->numberOfProduces;
    }

    public function numberOfWorkers(): int {
        return $this->numberOfWorkers;
    }

    public function numberOfClientsWaiting(): int {
        return $this->numberOfClientsWaiting;
    }

    public function cumulativeNumberOfTimedOutJobs(): int {
        return $this->cumulativeNumberOfTimedOutJobs;
    }

    public function cumulativeNumberOfJobs(): int {
        return $this->cumulativeNumberOfJobs;
    }

    public function cumulativeNumberOfConnections(): int {
        return $this->cumulativeNumberOfConnections;
    }
}
