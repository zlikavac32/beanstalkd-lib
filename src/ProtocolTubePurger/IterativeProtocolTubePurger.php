<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\ProtocolTubePurger;

use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\NotFoundException;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\ProtocolTubePurger;

/**
 * Removes all ready, delayed and buried jobs.
 */
class IterativeProtocolTubePurger implements ProtocolTubePurger
{

    private const ARBITRARY_PAUSE_TIME = 60 * 60 * 24;

    public function purge(Protocol $protocol, string ...$tubes): void
    {
        $currentTube = $protocol->listTubeUsed();

        try {
            $this->purgeTubes($protocol, $tubes);
        } finally {
            $protocol->useTube($currentTube);
        }
    }

    private function purgeTubes(Protocol $protocol, array $tubes): void
    {
        foreach ($tubes as $tubeName) {
            $protocol->useTube($tubeName);

            $this->purgeTube($protocol, $tubeName);
        }
    }

    private function purgeTube(Protocol $protocol, string $tubeName): void
    {
        $protocol->pauseTube($tubeName, self::ARBITRARY_PAUSE_TIME);

        try {
            $this->purgeSingleTubeState($protocol, function (Protocol $protocol): Job {
                return $protocol->peekReady();
            });
            $this->purgeSingleTubeState($protocol, function (Protocol $protocol): Job {
                return $protocol->peekDelayed();
            });
            $this->purgeSingleTubeState($protocol, function (Protocol $protocol): Job {
                return $protocol->peekBuried();
            });
        } finally {
            $protocol->pauseTube($tubeName, 0);
        }
    }

    private function purgeSingleTubeState(Protocol $protocol, callable $peekStrategy): void
    {
        while (true) {
            try {
                $job = $peekStrategy($protocol);
                assert($job instanceof Job);

                $protocol->delete($job->id());
            } catch (NotFoundException $e) {
                return;
            }
        }
    }
}
