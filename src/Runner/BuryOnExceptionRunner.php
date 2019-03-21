<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Runner;

use Throwable;
use Zlikavac32\BeanstalkdLib\InterruptException;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Runner;

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
        } catch (InterruptException $e) {
            throw $e;
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
        } catch (InterruptException $e) {
            // although this could overwrite existing exception that was caught, it's in respect to what this interrupt
            // means, and it means "quit without question" (assuming no catch-all block exists above, it should do so)
            throw $e;
        } catch (Throwable $e) {
            // ignore, nothing we can do
        }
    }
}
