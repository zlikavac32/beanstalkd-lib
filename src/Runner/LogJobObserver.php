<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Runner;

use Psr\Log\LoggerInterface;
use Throwable;
use Zlikavac32\BeanstalkdLib\JobHandle;
use function Zlikavac32\BeanstalkdLib\microTimeToHuman;

class LogJobObserver implements JobObserver
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function starting(JobHandle $jobHandle): void
    {
        $this->logger->info(
            sprintf(
                'Starting %d [%s]',
                $jobHandle->id(),
                $jobHandle->stats()
                    ->tubeName()
            )
        );
    }

    public function finished(JobHandle $jobHandle, float $duration): void
    {
        $this->logger->info(
            sprintf(
                'Finished %d [%s]',
                $jobHandle->id(),
                microTimeToHuman($duration)
            )
        );
    }

    public function failed(JobHandle $jobHandle, Throwable $cause, float $duration): void
    {
        $this->logger->error(
            sprintf(
                'Failed %d with "%s" [%s]',
                $jobHandle->id(),
                $cause->getMessage(),
                microTimeToHuman($duration)
            )
        );
    }
}
