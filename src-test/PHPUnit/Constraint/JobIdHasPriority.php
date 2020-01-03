<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Zlikavac32\BeanstalkdLib\Protocol;

class JobIdHasPriority extends Constraint
{

    private Protocol $protocol;

    private int $priority;

    public function __construct(Protocol $protocol, int $priority)
    {
        parent::__construct();

        $this->protocol = $protocol;
        $this->priority = $priority;
    }

    protected function matches($other): bool
    {
        if (!is_int($other)) {
            return false;
        }

        return $this->priority === $this->protocol->statsJob($other)['pri'];
    }

    protected function failureDescription($other): string
    {
        assert(is_int($other));

        return sprintf(
            'job %d has priority %d (actual priority %d)',
            $other,
            $this->priority,
            $this->protocol->statsJob($other)['pri']
        );
    }

    public function toString(): string
    {
        return sprintf('job has priority %d', $this->priority);
    }
}
