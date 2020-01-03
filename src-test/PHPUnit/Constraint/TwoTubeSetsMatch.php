<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint;

use Ds\Set;
use PHPUnit\Framework\Constraint\Constraint;

class TwoTubeSetsMatch extends Constraint
{

    private Set $tubeWatchList;

    public function __construct(Set $tubeWatchList)
    {
        parent::__construct();

        $this->tubeWatchList = $tubeWatchList;
    }

    protected function matches($other): bool
    {
        if (!$other instanceof Set) {
            return false;
        }

        return $other->count() === $this->tubeWatchList->count()
            &&
            $other->diff($this->tubeWatchList)
                ->count() === 0;
    }

    protected function failureDescription($other): string
    {
        return sprintf(
            '%s is same as [%s]',
            ($other instanceof Set) ? sprintf('[%s]', $other->join(', ')) : $this->exporter->export($other),
            $this->tubeWatchList->join(', ')
        );
    }

    public function toString(): string
    {
        return sprintf('each of [%s] is in set', $this->tubeWatchList->join(', '));
    }
}
