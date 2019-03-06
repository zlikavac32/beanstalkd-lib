<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Ds\Map;

/**
 * Implementation with predefined map of tubes and their job runners.
 */
class TubeMapJobDispatcher implements JobDispatcher {

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

    public function run(Client $client, int $numberOfJobsToRun): void {
        $tubeNames = $this->tubeRunners->keys();

        foreach ($tubeNames as $tubeName) {
            $client->watch($tubeName);
        }

        if (!$tubeNames->contains('default')) {
            $client->ignoreDefaultTube();
        }

        while (!$this->gracefulExit->inProgress() && $numberOfJobsToRun > 0) {
            $this->reserveAndRun($client);

            $numberOfJobsToRun--;
        }
    }

    private function reserveAndRun(Client $client): void {
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
}
