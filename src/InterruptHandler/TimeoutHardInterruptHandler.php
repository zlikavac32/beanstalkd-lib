<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\InterruptHandler;

use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\BeanstalkdLib\InterruptHandler;

/**
 * Schedules alarm handler that should perform hard async interrupt.
 */
class TimeoutHardInterruptHandler implements InterruptHandler {

    /**
     * @var int
     */
    private $timeout;
    /**
     * @var AlarmScheduler
     */
    private $scheduler;
    /**
     * @var AlarmHandler
     */
    private $alarmHandler;

    public function __construct(AlarmScheduler $scheduler, AlarmHandler $alarmHandler, int $timeout) {
        $this->timeout = $timeout;
        $this->scheduler = $scheduler;
        $this->alarmHandler = $alarmHandler;
    }

    public function handle(): void {
        $this->scheduler->schedule($this->timeout, $this->alarmHandler);
    }
}
