<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Runner;

use Exception;
use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Runner;
use Zlikavac32\BeanstalkdLib\Runner\DeleteJobOnSuccessRunner;

class DeleteJobOnSuccessRunnerSpec extends ObjectBehavior
{

    public function let(Runner $runner): void
    {
        $this->beConstructedWith($runner);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(DeleteJobOnSuccessRunner::class);
    }

    public function it_should_delete_job_when_no_exception_is_thrown(Runner $runner, JobHandle $jobHandle): void
    {
        $runner->run($jobHandle)
            ->shouldBeCalled();

        $jobHandle->delete()
            ->shouldBeCalled();

        $this->run($jobHandle);
    }

    public function it_should_not_delete_job_when_exception_is_thrown(Runner $runner, JobHandle $jobHandle): void
    {
        $e = new Exception();

        $runner->run($jobHandle)
            ->willThrow($e);

        $jobHandle->delete()
            ->shouldNotBeCalled();

        $this->shouldThrow($e)
            ->duringRun($jobHandle);
    }
}
