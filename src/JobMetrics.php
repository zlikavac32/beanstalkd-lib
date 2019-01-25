<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class JobMetrics {

    /**
     * @var int
     */
    private $numberOfReserves;
    /**
     * @var int
     */
    private $numberOfTimeouts;
    /**
     * @var int
     */
    private $numberOfReleases;
    /**
     * @var int
     */
    private $numberOfBuries;
    /**
     * @var int
     */
    private $numberOfKicks;

    public function __construct(
        int $numberOfReserves,
        int $numberOfTimeouts,
        int $numberOfReleases,
        int $numberOfBuries,
        int $numberOfKicks
    ) {
        $this->numberOfReserves = $numberOfReserves;
        $this->numberOfTimeouts = $numberOfTimeouts;
        $this->numberOfReleases = $numberOfReleases;
        $this->numberOfBuries = $numberOfBuries;
        $this->numberOfKicks = $numberOfKicks;
    }

    public function numberOfReserves(): int {
        return $this->numberOfReserves;
    }

    public function numberOfTimeouts(): int {
        return $this->numberOfTimeouts;
    }

    public function numberOfReleases(): int {
        return $this->numberOfReleases;
    }

    public function numberOfBuries(): int {
        return $this->numberOfBuries;
    }

    public function numberOfKicks(): int {
        return $this->numberOfKicks;
    }
}
