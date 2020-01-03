<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Zlikavac32\BeanstalkdLib\Protocol;

class JobIdHasDelay extends Constraint
{

    private Protocol $protocol;

    private int $delay;

    public function __construct(Protocol $protocol, int $delay)
    {
        parent::__construct();

        $this->protocol = $protocol;
        $this->delay = $delay;
    }

    protected function matches($other): bool
    {
        if (!is_int($other)) {
            return false;
        }

        return $this->delay === $this->protocol->statsJob($other)['delay'];
    }

    protected function failureDescription($other): string
    {
        assert(is_int($other));

        return sprintf(
            'job %d has delay %d (actual delay %d)',
            $other,
            $this->delay,
            $this->protocol->statsJob($other)['delay']
        );
    }

    public function toString(): string
    {
        return sprintf('job has delay %d', $this->delay);
    }
}
