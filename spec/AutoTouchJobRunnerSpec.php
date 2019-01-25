<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib;

use PhpSpec\ObjectBehavior;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\BeanstalkdLib\AutoTouchJobRunner;
use Zlikavac32\BeanstalkdLib\Client;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Runner;

class AutoTouchJobRunnerSpec extends ObjectBehavior {

    public function let(Runner $runner, Client $client, AlarmScheduler $scheduler): void {
        $this->beConstructedWith($runner, $client, $scheduler);
    }

    public function it_is_initializable(): void {
        $this->shouldHaveType(AutoTouchJobRunner::class);
    }

    // @todo: what with new?
}
