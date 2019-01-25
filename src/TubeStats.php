<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class TubeStats {

    /**
     * @var string
     */
    private $tubeName;
    /**
     * @var int
     */
    private $pauseDuration;
    /**
     * @var int
     */
    private $remainingPauseTime;
    /**
     * @var TubeMetrics
     */
    private $metrics;

    public function __construct(
        string $tubeName,
        int $pauseDuration,
        int $remainingPauseTime,
        TubeMetrics $metrics
    ) {
        $this->tubeName = $tubeName;
        $this->pauseDuration = $pauseDuration;
        $this->remainingPauseTime = $remainingPauseTime;
        $this->metrics = $metrics;
    }

    public function tubeName(): string {
        return $this->tubeName;
    }

    public function pauseDuration(): int {
        return $this->pauseDuration;
    }

    public function remainingPauseTime(): int {
        return $this->remainingPauseTime;
    }

    public function metrics(): TubeMetrics {
        return $this->metrics;
    }
}
