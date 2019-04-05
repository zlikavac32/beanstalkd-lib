<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Adapter\Symfony\Console;

use Zlikavac32\AlarmScheduler\AlarmScheduler;

class ManageAlarmSchedulerEventListener
{

    /**
     * @var AlarmScheduler
     */
    private $alarmScheduler;

    public function __construct(AlarmScheduler $alarmScheduler)
    {
        $this->alarmScheduler = $alarmScheduler;
    }

    public function onConsoleCommand(): void
    {
        $this->alarmScheduler->start();
    }

    public function onConsoleTerminate(): void
    {
        $this->alarmScheduler->finish();
    }
}
