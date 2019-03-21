<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Runner;

use Exception;
use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\InterruptException;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Runner;
use Zlikavac32\BeanstalkdLib\Runner\ReleaseOnInterruptExceptionRunner;

class ReleaseOnInterruptExceptionRunnerSpec extends ObjectBehavior {

    public function let(Runner $runner): void {
        $this->beConstructedWith($runner);
    }

    public function it_is_initializable(): void {
        $this->shouldHaveType(ReleaseOnInterruptExceptionRunner::class);
    }

    public function it_should_not_release_job_when_no_exception_is_caught(Runner $runner, JobHandle $jobHandle): void {
        $runner->run($jobHandle)->shouldBeCalled();

        $jobHandle->release()
            ->shouldNotBeCalled();

        $this->run($jobHandle);
    }

    public function it_should_not_release_job_when_other_exception_is_caught_and_not_release_anything(
        Runner $runner,
        JobHandle $jobHandle
    ): void {
        $e = new Exception();

        $runner->run($jobHandle)->willThrow($e);

        $jobHandle->release()
            ->shouldNotBeCalled();

        $this->shouldThrow($e)->duringRun($jobHandle);
    }

    public function it_should_release_job_when_interrupt_exception_is_caught(
        Runner $runner,
        JobHandle $jobHandle
    ): void {
        $e = new InterruptException();

        $runner->run($jobHandle)->willThrow($e);

        $jobHandle->release()
            ->shouldBeCalled();

        $this->shouldThrow($e)->duringRun($jobHandle);
    }
}
