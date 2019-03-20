<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Ds\Set;

interface JobDispatcher {

    /**
     * After this method returns, watched tubes are considered to be in an undefined state.
     *
     * @param Client $client Fresh instance that watches only default tube
     * @param Set|string[] $tubesToWatch Set of tube names to watch
     */
    public function run(Client $client, Set $tubesToWatch, int $numberOfJobsToRun): void;

    /**
     * Set of tube names known by this job dispatcher
     *
     * @return Set|string[]
     */
    public function knownTubes(): Set;
}
