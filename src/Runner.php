<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

interface Runner
{

    /**
     * Runner is responsible for job deletion, burying, rescheduling etc.
     */
    public function run(JobHandle $jobHandle): void;
}
