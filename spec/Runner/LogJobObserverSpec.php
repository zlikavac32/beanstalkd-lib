<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Runner;

use Exception;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\JobStats;
use Zlikavac32\BeanstalkdLib\Runner\LogJobObserver;

class LogJobObserverSpec extends ObjectBehavior
{

    public function let(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(LogJobObserver::class);
    }

    public function it_should_output_starting_event(
        LoggerInterface $logger,
        JobHandle $jobHandle,
        JobStats $jobStats
    ): void {
        $jobHandle->id()
            ->willReturn(32);
        $jobHandle->stats()
            ->willReturn($jobStats);

        $jobStats->tubeName()
            ->willReturn('foo.bar');

        $logger->info('Starting 32 [foo.bar]')
            ->shouldBeCalled();

        $this->starting($jobHandle);
    }

    public function it_should_output_finished_event(
        LoggerInterface $logger,
        JobHandle $jobHandle
    ): void {
        $jobHandle->id()
            ->willReturn(32);

        $logger->info('Finished 32 [4 min 2 s]')
            ->shouldBeCalled();

        $this->finished($jobHandle, 4 * 60 + 2);
    }

    public function it_should_output_failed_event(LoggerInterface $logger, JobHandle $jobHandle): void
    {
        $jobHandle->id()
            ->willReturn(32);

        $logger->error('Failed 32 with "Test failure" [4 ms]')
            ->shouldBeCalled();

        $this->failed($jobHandle, new Exception('Test failure'), 4e-3);
    }
}
