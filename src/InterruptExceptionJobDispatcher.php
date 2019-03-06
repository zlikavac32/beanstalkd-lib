<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;

/**
 * Job dispatcher that uses SIGUSR1 for async hard interrupt.
 *
 * When dispatcher is run, it takes over control of SIGUSR1 handling,
 * and upon alarm handling, sends to itself SIGUSR1.
 *
 * Before method is finished, previous signal handler for SIGUSR1 is
 * reinstalled.
 */
class InterruptExceptionJobDispatcher implements JobDispatcher, AlarmHandler {

    /**
     * @var JobDispatcher
     */
    private $jobDispatcher;

    public function __construct(JobDispatcher $jobDispatcher) {
        $this->jobDispatcher = $jobDispatcher;
    }

    public function handle(AlarmScheduler $scheduler): void {
        posix_kill(getmypid(), SIGUSR1);
    }

    public function run(Client $client, int $numberOfJobsToRun): void {
        $oldSignalHandler = pcntl_signal_get_handler(SIGUSR1);

        pcntl_signal(SIGUSR1, function (): void {
            throw new InterruptException();
        });

        try {
            $this->jobDispatcher->run($client, $numberOfJobsToRun);
        } finally {
            pcntl_signal(SIGUSR1, $oldSignalHandler);
        }
    }
}
