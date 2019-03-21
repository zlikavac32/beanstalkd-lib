<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Runner;

use Exception;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Runner;
use Zlikavac32\BeanstalkdLib\Runner\JobObserver;
use Zlikavac32\BeanstalkdLib\Runner\JobObserverRunner;

class JobObserverRunnerSpec extends ObjectBehavior {

    public function let(Runner $runner, JobObserver $jobObserver): void {
        $this->beConstructedWith($runner, $jobObserver);
    }

    public function it_is_initializable(): void {
        $this->shouldHaveType(JobObserverRunner::class);
    }

    public function it_should_signal_finish_on_success(Runner $runner, JobObserver $jobObserver, JobHandle $jobHandle): void {
        $runner->run($jobHandle)->shouldBeCalled();

        $jobObserver->starting($jobHandle)->shouldBeCalled();
        // miliseconds should be ok
        $jobObserver->finished($jobHandle, Argument::that(function (float $value): bool {
            return $value < 1e-3;
        }));

        $this->run($jobHandle);
    }

    public function it_should_signal_failed_on_fail(Runner $runner, JobObserver $jobObserver, JobHandle $jobHandle): void {
        $e = new Exception();

        $runner->run($jobHandle)->willThrow($e);

        $jobObserver->starting($jobHandle)->shouldBeCalled();
        // miliseconds should be ok
        $jobObserver->failed($jobHandle, $e, Argument::that(function (float $value): bool {
            return $value < 1e-3;
        }));

        $this->shouldThrow($e)->duringRun($jobHandle);
    }
}
