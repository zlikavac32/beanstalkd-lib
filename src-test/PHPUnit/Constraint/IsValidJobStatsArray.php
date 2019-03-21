<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

// @todo: :)
class IsValidJobStatsArray extends Constraint
{

    protected function matches($other): bool
    {
        return is_array($other);
    }

    protected function failureDescription($other): string
    {
        return sprintf(
            '%s contains valid job stats array',
            $this->exporter->export($other)
        );
    }

    public function toString(): string
    {
        return sprintf('contains valid job stats array');
    }
}
