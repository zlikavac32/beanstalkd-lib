<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Ds\Map;
use LogicException;

class CommandMetrics
{

    /**
     * @var Map
     */
    private $stats;

    public function __construct(Map $stats)
    {
        $this->assertThatMapContainsEveryElement($stats);
        $this->stats = $stats;
    }

    public function numberOf(Command $command): int
    {
        return $this->stats->get($command);
    }

    private function assertThatMapContainsEveryElement(Map $stats)
    {
        foreach (Command::values() as $command) {
            if ($stats->hasKey($command)) {
                continue;
            }

            throw new LogicException(\sprintf('Stats for command "%s" are missing', $command));
        }
    }
}
