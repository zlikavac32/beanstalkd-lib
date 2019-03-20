<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Throwable;

class CompositeJobObserver implements JobObserver {

    /**
     * @var JobObserver[]
     */
    private $jobObservers;

    public function __construct(JobObserver ...$jobObservers) {
        $this->jobObservers = $jobObservers;
    }

    public function starting(JobHandle $jobHandle): void {
        foreach ($this->jobObservers as $jobObserver) {
            $jobObserver->starting($jobHandle);
        }
    }

    public function finished(JobHandle $jobHandle, float $duration): void {
        foreach ($this->jobObservers as $jobObserver) {
            $jobObserver->finished($jobHandle, $duration);
        }
    }

    public function failed(JobHandle $jobHandle, Throwable $cause, float $duration): void {
        foreach ($this->jobObservers as $jobObserver) {
            $jobObserver->failed($jobHandle, $cause, $duration);
        }
    }
}
