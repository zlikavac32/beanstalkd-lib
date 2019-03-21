<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Runner;

use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\BeanstalkdLib\AlarmHandler\TouchJobAlarmHandler;
use Zlikavac32\BeanstalkdLib\Client;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Runner;

/**
 * Runner that initializes auto-touch functionality. Slightly before job is to be
 * expired, touch will be called from the signal handler.
 */
class AutoTouchRunner implements Runner
{

    /**
     * @var Runner
     */
    private $runner;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var AlarmHandler
     */
    private $scheduledHandler;
    /**
     * @var AlarmScheduler
     */
    private $scheduler;

    public function __construct(Runner $runner, Client $client, AlarmScheduler $scheduler)
    {
        $this->runner = $runner;
        $this->client = $client;
        $this->scheduler = $scheduler;
    }

    public function run(JobHandle $jobHandle): void
    {
        $this->scheduleAlarmHandlerIfPossible($jobHandle);

        try {
            $this->runner->run($jobHandle);
        } finally {
            $this->clearAnySchedules();
        }
    }

    private function scheduleAlarmHandlerIfPossible(JobHandle $jobHandle): void
    {
        $this->scheduledHandler = null;

        $handler = new TouchJobAlarmHandler($this->client, $jobHandle->id());

        if (!$handler->scheduled($this->scheduler, $jobHandle->stats()
            ->timeLeft())) {
            // nothing we can do
            return;
        }

        $this->scheduledHandler = $handler;
    }

    private function clearAnySchedules(): void
    {
        if (null === $this->scheduledHandler) {
            return;
        }

        $this->scheduler->remove($this->scheduledHandler);
    }
}
