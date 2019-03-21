<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Runner;

use Throwable;
use Zlikavac32\BeanstalkdLib\InterruptException;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Runner;

/**
 * Runner that releases job when an interrupt exception is caught.
 */
class ReleaseOnInterruptExceptionRunner implements Runner
{

    /**
     * @var Runner
     */
    private $runner;

    public function __construct(Runner $runner)
    {
        $this->runner = $runner;
    }

    public function run(JobHandle $jobHandle): void
    {
        try {
            $this->runner->run($jobHandle);
        } catch (InterruptException $e) {
            $this->releaseJob($jobHandle);

            throw $e;
        }
    }

    private function releaseJob(JobHandle $job): void
    {
        try {
            $job->release();
        } catch (Throwable $e) {
            // If we caught interrupt exception, keep first interrupt exception
            // On other cases, there is just nothing we can od
        }
    }
}
