<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Runner;

use Throwable;
use Zlikavac32\BeanstalkdLib\JobHandle;

interface JobObserver
{

    public function starting(JobHandle $jobHandle): void;

    public function finished(JobHandle $jobHandle, float $duration): void;

    public function failed(JobHandle $jobHandle, Throwable $cause, float $duration): void;
}
