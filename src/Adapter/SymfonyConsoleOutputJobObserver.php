<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Adapter;

use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\JobObserver;
use function sprintf;
use function Zlikavac32\BeanstalkdLib\microTimeToHuman;

class SymfonyConsoleOutputJobObserver implements JobObserver {

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output) {
        $this->output = $output;
    }

    public function starting(JobHandle $jobHandle): void {
        $this->output->writeln(sprintf('Starting job %d', $jobHandle->id()));
    }

    public function finished(JobHandle $jobHandle, float $duration): void {
        $this->output->writeln(sprintf('Finished job %d in [%s]', $jobHandle->id(), microTimeToHuman($duration)));
    }

    public function failed(JobHandle $jobHandle, Throwable $cause, float $duration): void {
        $this->output->writeln(
            sprintf(
                'Failed job %d with "%s" in [%s]',
                $jobHandle->id(),
                $cause->getMessage(),
                microTimeToHuman($duration)
            )
        );
    }
}