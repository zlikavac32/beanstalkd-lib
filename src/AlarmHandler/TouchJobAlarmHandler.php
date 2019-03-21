<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\AlarmHandler;

use Throwable;
use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\BeanstalkdLib\Client;
use Zlikavac32\BeanstalkdLib\InterruptException;

/**
 * Alarm handler used to periodically touch a job.
 *
 * Do note that it's run from the signal handler, so
 * use of shared socket must be safe.
 */
class TouchJobAlarmHandler implements AlarmHandler
{

    /**
     * @var Client
     */
    private $client;
    /**
     * @var int
     */
    private $jobId;

    public function __construct(Client $client, int $jobId)
    {
        $this->client = $client;
        $this->jobId = $jobId;
    }

    public function handle(AlarmScheduler $scheduler): void
    {
        try {
            $jobHandle = $this->client->peek($this->jobId);

            $jobHandle->touch();

            $this->scheduled($scheduler,
                $jobHandle->stats()
                    ->timeLeft()
            );
        } catch (InterruptException $e) {
            throw $e;
        } catch (Throwable $e) {
            return;
        }
    }

    /**
     * Tries to schedule this handler into $scheduler. If $timeLeft is so small that
     * it makes no sense to touch it, it does not schedule this handler.
     *
     * @param AlarmScheduler $scheduler
     * @param int $timeLeft
     *
     * @return bool True if handler was scheduled, false if not
     */
    public function scheduled(AlarmScheduler $scheduler, int $timeLeft): bool
    {
        // (1 + 1 + 1) => (minimum sleep + beanstalkd server + precision of sleep)
        if ($timeLeft < 3) {
            return false;
        }

        $scheduler->schedule($timeLeft - 2, $this);

        return true;
    }
}
