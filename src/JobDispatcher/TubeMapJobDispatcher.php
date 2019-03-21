<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\JobDispatcher;

use Ds\Map;
use Ds\Set;
use LogicException;
use Zlikavac32\BeanstalkdLib\Client;
use Zlikavac32\BeanstalkdLib\GracefulExit;
use Zlikavac32\BeanstalkdLib\JobDispatcher;
use Zlikavac32\BeanstalkdLib\ReserveInterruptedException;

/**
 * Implementation with predefined map of tubes and their job runners.
 */
class TubeMapJobDispatcher implements JobDispatcher
{

    /**
     * @var Map|\Zlikavac32\BeanstalkdLib\Runner[]
     */
    private $tubeRunners;
    /**
     * @var GracefulExit
     */
    private $gracefulExit;

    public function __construct(
        Map $tubeRunners,
        GracefulExit $gracefulExit
    ) {
        $this->tubeRunners = $tubeRunners;
        $this->gracefulExit = $gracefulExit;
    }

    public function run(Client $client, Set $tubesToWatch, int $numberOfJobsToRun): void
    {
        $knownTubeNames = $this->tubeRunners->keys();

        foreach ($tubesToWatch as $tubeName) {
            if (!$knownTubeNames->contains($tubeName)) {
                throw new LogicException(sprintf('Tube %s is not known by this runner. Known tubes are [%s]', $tubeName,
                    $knownTubeNames->join(', ')));
            }

            $client->watch($tubeName);
        }

        if (!$tubesToWatch->contains('default')) {
            $client->ignoreDefaultTube();
        }

        while (!$this->gracefulExit->inProgress() && $numberOfJobsToRun > 0) {
            $this->reserveAndRun($client);

            $numberOfJobsToRun--;
        }
    }

    private function reserveAndRun(Client $client): void
    {
        try {
            $job = $client->reserve();
        } catch (ReserveInterruptedException $e) {
            return;
        }

        $tubeName = $job->stats()
            ->tubeName();

        /** @var \Zlikavac32\BeanstalkdLib\Runner $runner */
        $runner = $this->tubeRunners->get($tubeName);

        // one more check, just before we let control to the runner
        if ($this->gracefulExit->inProgress()) {
            return;
        }

        $runner->run($job);
    }

    /**
     * @inheritdoc
     */
    public function knownTubes(): Set
    {
        return $this->tubeRunners->keys();
    }
}
