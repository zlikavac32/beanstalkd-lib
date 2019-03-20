<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Adapter;

use Exception;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Output\OutputInterface;
use Zlikavac32\BeanstalkdLib\Adapter\SymfonyConsoleOutputJobObserver;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\JobStats;

class SymfonyConsoleOutputJobObserverSpec extends ObjectBehavior {

    public function let(OutputInterface $output): void {
        $this->beConstructedWith($output);
    }

    public function it_is_initializable(): void {
        $this->shouldHaveType(SymfonyConsoleOutputJobObserver::class);
    }

    public function it_should_output_starting_event(OutputInterface $output, JobHandle $jobHandle, JobStats $jobStats): void {
        $jobHandle->id()->willReturn(32);
        $jobHandle->stats()->willReturn($jobStats);

        $jobStats->tubeName()->willReturn('foo.bar');

        $output->writeln('Starting job 32 (tube foo.bar)')->shouldBeCalled();

        $this->starting($jobHandle);
    }

    public function it_should_output_finished_event(OutputInterface $output, JobHandle $jobHandle, JobStats $jobStats): void {
        $jobHandle->id()->willReturn(32);

        $output->writeln('Finished job 32 in [4 min 2 s]')->shouldBeCalled();

        $this->finished($jobHandle, 4 * 60 + 2);
    }

    public function it_should_output_failed_event(OutputInterface $output, JobHandle $jobHandle): void {
        $jobHandle->id()->willReturn(32);

        $output->writeln('Failed job 32 with "Test failure" in [4 ms]')->shouldBeCalled();

        $this->failed($jobHandle, new Exception('Test failure'), 4e-3);
    }
}
