<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Runner;

use Throwable;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Runner;

class JobObserverRunner implements Runner {

    /**
     * @var Runner
     */
    private $runner;
    /**
     * @var JobObserver
     */
    private $jobObserver;
    /**
     * @var float
     */
    private $jobStartedAt;

    public function __construct(Runner $runner, JobObserver $jobObserver) {
        $this->runner = $runner;
        $this->jobObserver = $jobObserver;
    }

    public function run(JobHandle $jobHandle): void {
        $this->jobObserver->starting($jobHandle);

        $this->jobStartedAt = microtime(true);

        try {
            $this->runner->run($jobHandle);

            $this->jobObserver->finished($jobHandle, microtime(true) - $this->jobStartedAt);
        } catch (Throwable $e) {
            $this->jobObserver->failed($jobHandle, $e, microtime(true) - $this->jobStartedAt);

            throw $e;
        }
    }
}
