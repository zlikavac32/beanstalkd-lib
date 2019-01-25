<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib;

use Exception;
use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\BuryOnExceptionRunner;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Runner;
use Zlikavac32\BeanstalkdLib\ThrowableAuthority;

class BuryOnExceptionRunnerSpec extends ObjectBehavior {

    public function let(Runner $runner, ThrowableAuthority $throwableAuthority): void {
        $this->beConstructedWith($runner, $throwableAuthority);
    }

    public function it_is_initializable(): void {
        $this->shouldHaveType(BuryOnExceptionRunner::class);
    }

    public function it_should_not_bury_job_when_everything_ok(Runner $runner, JobHandle $jobHandle): void {
        $runner->run($jobHandle)
            ->shouldBecalled();
        $jobHandle->bury()
            ->shouldNotBeCalled();

        $this->run($jobHandle);
    }

    public function it_should_bury_job_without_rethrow_when_authority_says_so(
        Runner $runner,
        ThrowableAuthority $throwableAuthority,
        JobHandle $jobHandle
    ): void {
        $e = new Exception('foo');

        $throwableAuthority->shouldRethrow($e)
            ->willReturn(false);

        $runner->run($jobHandle)->willThrow($e);

        $jobHandle->bury()->shouldBeCalled();

        $this->run($jobHandle);
    }

    public function it_should_not_fail_if_bury_fails(
        Runner $runner,
        ThrowableAuthority $throwableAuthority,
        JobHandle $jobHandle
    ): void {
        $e = new Exception('foo');

        $throwableAuthority->shouldRethrow($e)
            ->willReturn(false);

        $runner->run($jobHandle)->willThrow($e);

        $jobHandle->bury()->shouldBeCalled()->willThrow(new Exception('bar'));

        $this->run($jobHandle);
    }

    public function it_should_bury_job_with_rethrow_when_authority_says_so(
        Runner $runner,
        ThrowableAuthority $throwableAuthority,
        JobHandle $jobHandle
    ): void {
        $e = new Exception('foo');

        $throwableAuthority->shouldRethrow($e)
            ->willReturn(true);

        $runner->run($jobHandle)->willThrow($e);

        $jobHandle->bury()->shouldBeCalled();

        $this->shouldThrow($e)->duringRun($jobHandle);
    }
}
