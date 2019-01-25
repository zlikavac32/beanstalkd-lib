<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Throwable;

/**
 * Runner that buries job when an exception is caught. Whether caught
 * exception should be rethrown is determined by the $throwableAuthority.
 */
class BuryOnExceptionRunner implements Runner {

    /**
     * @var Runner
     */
    private $runner;
    /**
     * @var ThrowableAuthority
     */
    private $throwableAuthority;

    public function __construct(Runner $runner, ThrowableAuthority $throwableAuthority) {
        $this->runner = $runner;
        $this->throwableAuthority = $throwableAuthority;
    }

    public function run(JobHandle $jobHandle): void {
        try {
            $this->runner->run($jobHandle);
        } catch (Throwable $e) {
            $this->buryJob($jobHandle);

            if ($this->throwableAuthority->shouldRethrow($e)) {
                throw $e;
            }
        }
    }

    private function buryJob(JobHandle $job): void {
        try {
            $job->bury();
        } catch (Throwable $e) {
            // ignore, nothing we can do
        }
    }
}
