<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class TubeMetrics {

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
    private $cumulativeNumberOfJobs;
    /**
     * @var int
     */
    private $numberOfClientsUsing;
    /**
     * @var int
     */
    private $numberOfClientsWaiting;
    /**
     * @var int
     */
    private $numberOfClientsWatching;
    /**
     * @var int
     */
    private $numberOfDeleteCommands;
    /**
     * @var int
     */
    private $numberOfPauseTubeCommands;

    public function __construct(
        int $numberOfUrgentJobs,
        int $numberOfReadyJobs,
        int $numberOfReservedJobs,
        int $numberOfDelayedJobs,
        int $numberOfBuriedJobs,
        int $cumulativeNumberOfJobs,
        int $numberOfClientsUsing,
        int $numberOfClientsWaiting,
        int $numberOfClientsWatching,
        int $numberOfDeleteCommands,
        int $numberOfPauseTubeCommands
    ) {
        $this->numberOfUrgentJobs = $numberOfUrgentJobs;
        $this->numberOfReadyJobs = $numberOfReadyJobs;
        $this->numberOfReservedJobs = $numberOfReservedJobs;
        $this->numberOfDelayedJobs = $numberOfDelayedJobs;
        $this->numberOfBuriedJobs = $numberOfBuriedJobs;
        $this->cumulativeNumberOfJobs = $cumulativeNumberOfJobs;
        $this->numberOfClientsUsing = $numberOfClientsUsing;
        $this->numberOfClientsWaiting = $numberOfClientsWaiting;
        $this->numberOfClientsWatching = $numberOfClientsWatching;
        $this->numberOfDeleteCommands = $numberOfDeleteCommands;
        $this->numberOfPauseTubeCommands = $numberOfPauseTubeCommands;
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

    public function cumulativeNumberOfJobs(): int {
        return $this->cumulativeNumberOfJobs;
    }

    public function numberOfClientsUsing(): int {
        return $this->numberOfClientsUsing;
    }

    public function numberOfClientsWaiting(): int {
        return $this->numberOfClientsWaiting;
    }

    public function numberOfClientsWatching(): int {
        return $this->numberOfClientsWatching;
    }

    public function numberOfDeleteCommands(): int {
        return $this->numberOfDeleteCommands;
    }

    public function numberOfPauseTubeCommands(): int {
        return $this->numberOfPauseTubeCommands;
    }
}
