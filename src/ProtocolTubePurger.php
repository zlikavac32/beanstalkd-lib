<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Ds\Sequence;
use Ds\Set;

interface ProtocolTubePurger
{

    /**
     * @param Sequence|string[] $tubes
     * @param Set|JobState[] $states
     */
    public function purge(Protocol $protocol, Sequence $tubes, Set $states): void;
}
