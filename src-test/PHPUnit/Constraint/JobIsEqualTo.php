<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Zlikavac32\BeanstalkdLib\Job;

class JobIsEqualTo extends Constraint
{

    private Job $job;

    public function __construct(Job $job)
    {
        parent::__construct();

        $this->job = $job;
    }

    protected function matches($other): bool
    {
        if (!$other instanceof Job) {
            return false;
        }

        if ($other->id() !== $this->job->id()) {
            return false;
        }

        return $other->payload() === $this->job->payload();
    }

    protected function failureDescription($other): string
    {
        return sprintf(
            'job %s is equal to (%d, "%s")',
            $this->exporter->export($other),
            $this->job->id(),
            $this->job->payload()
        );
    }

    public function toString(): string
    {
        return sprintf('is equal to job (%d, "%s")', $this->job->id(), $this->job->payload());
    }
}
