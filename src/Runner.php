<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

interface Runner {

    public function run(JobHandle $jobHandle): void;
}
