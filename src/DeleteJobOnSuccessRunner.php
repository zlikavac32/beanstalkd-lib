<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

/**
 * Runner that deletes job if no exception was thrown
 */
class DeleteJobOnSuccessRunner implements Runner {

    /**
     * @var Runner
     */
    private $runner;

    public function __construct(Runner $runner) {
        $this->runner = $runner;
    }

    public function run(JobHandle $jobHandle): void {
        $this->runner->run($jobHandle);

        $jobHandle->delete();
    }
}
