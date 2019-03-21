<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Zlikavac32\BeanstalkdLib\JobState;
use Zlikavac32\BeanstalkdLib\Protocol;

class JobIdIsInState extends Constraint
{

    /**
     * @var Protocol
     */
    private $protocol;
    /**
     * @var JobState
     */
    private $jobState;

    public function __construct(Protocol $protocol, JobState $jobState)
    {
        parent::__construct();

        $this->protocol = $protocol;
        $this->jobState = $jobState;
    }

    protected function matches($other): bool
    {
        if (!is_int($other)) {
            return false;
        }

        return $this->currentState($other) === $this->jobState;
    }

    protected function failureDescription($other): string
    {
        assert(is_int($other));

        return sprintf(
            'job %d is in state %s (it is %s)',
            $other,
            $this->jobState,
            $this->currentState($other)
        );
    }

    private function currentState(int $jobId): JobState
    {
        return JobState::valueOf(strtoupper($this->protocol->statsJob($jobId)['state']));
    }

    public function toString(): string
    {
        return sprintf(
            'job is %s',
            $this->jobState
        );
    }
}
