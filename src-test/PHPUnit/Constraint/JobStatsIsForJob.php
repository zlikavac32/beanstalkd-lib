<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\JobStats;
use Zlikavac32\BeanstalkdLib\Protocol;

class JobStatsIsForJob extends Constraint {

    /**
     * @var Job
     */
    private $job;
    /**
     * @var Protocol
     */
    private $protocol;

    public function __construct(Protocol $protocol, Job $job) {
        parent::__construct();

        $this->job = $job;
        $this->protocol = $protocol;
    }

    protected function matches($other): bool {
        return $other instanceof JobStats;
    }

    protected function failureDescription($other): string {
        return sprintf(
            '%s is job stats for %d',
            $this->exporter->export($other),
            $this->job->id()
        );
    }

    public function toString(): string {
        return sprintf('is job stats for %d', $this->job->id());
    }
}
