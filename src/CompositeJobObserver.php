<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use SplObjectStorage;
use Throwable;

class CompositeJobObserver implements JobObserver {

    /**
     * @var JobObserver[]
     */
    private $jobObservers;
    /**
     * @var int
     */
    private $nextIndex;
    /**
     * @var SplObjectStorage
     */
    private $observerStorage;

    public function __construct(JobObserver ...$jobObservers) {
        $this->jobObservers = $jobObservers;
        $this->observerStorage = new SplObjectStorage();

        foreach ($this->jobObservers as $index => $observer) {
            $this->observerStorage->attach($observer, $index);
        }

        $this->nextIndex = count($jobObservers);
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

    public function append(JobObserver $observer): void {
        $this->jobObservers[$this->nextIndex] = $observer;
        $this->observerStorage->attach($observer, $this->nextIndex);
        $this->nextIndex++;
    }

    public function has(JobObserver $observer): bool {
        return $this->observerStorage->contains($observer);
    }

    public function remove(JobObserver $observer): void {
        if (!$this->has($observer)) {
            return ;
        }

        unset($this->jobObservers[$this->observerStorage->offsetGet($observer)]);
        $this->observerStorage->detach($observer);
    }
}
