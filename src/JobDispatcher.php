<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

interface JobDispatcher {

    /**
     * After this method returns, watched tubes are considered to be in an undefined state.
     *
     * @param Client $client Fresh instance that watches only default tube
     */
    public function run(Client $client): void;
}
