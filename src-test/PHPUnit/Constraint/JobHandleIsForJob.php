<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\JobHandle;

class JobHandleIsForJob extends Constraint
{

    private Job $job;

    public function __construct(Job $job)
    {
        parent::__construct();

        $this->job = $job;
    }

    protected function matches($other): bool
    {
        if (!$other instanceof JobHandle) {
            return false;
        }

        return $other->id() === $this->job->id();
    }

    protected function failureDescription($other): string
    {
        if (!$other instanceof JobHandle) {
            return sprintf('is instance of %s', JobHandle::class);
        }

        return sprintf(
            'job handle %d is for %d',
            $other->id(),
            $this->job->id()
        );
    }

    public function toString(): string
    {
        return sprintf('is job handle for %d', $this->job->id());
    }
}
