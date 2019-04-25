<?php

declare(strict_types=1);

use Ds\Set;
use Zlikavac32\BeanstalkdLib\GracefulExit;
use Zlikavac32\BeanstalkdLib\InterruptHandler\CompositeInterruptHandler;
use Zlikavac32\BeanstalkdLib\InterruptHandler\HardInterruptHandler;
use Zlikavac32\BeanstalkdLib\InterruptHandler\TimeoutHardInterruptHandler;
use Zlikavac32\BeanstalkdLib\JobDispatcher\TubeMapJobDispatcher;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Runner\BuryOnExceptionRunner;
use Zlikavac32\BeanstalkdLib\Runner\ThrowNoneThrowableAuthority;
use Zlikavac32\BeanstalkdLib\SignalHandlerInstaller;

require_once __DIR__.'/common.php';

/**
 * Worker implementation that simulates some commit indexing. Try Ctrl+C
 * once when job is running. It will complete and runner will then exit.
 * Second Ctrl+C will cause exception to be thrown to represent hard
 * interrupt.
 */
class IndexProjectCommitRunner implements \Zlikavac32\BeanstalkdLib\Runner
{

    /**
     * @inheritdoc
     */
    public function run(JobHandle $jobHandle): void
    {
        $projectCommit = $jobHandle->payload();

        assert($projectCommit instanceof ProjectCommit);

        // simulate commit processing between 1 and 10 seconds
        $numberOfSecondsToWorkFor = crc32($projectCommit->commit()) % 10 + 1;
        $until = microtime(true) + $numberOfSecondsToWorkFor;

        echo 'Will run for ', $numberOfSecondsToWorkFor, ' seconds', "\n";

        while (microtime(true) < $until) {
            password_hash(sha1((string)microtime(true)), PASSWORD_DEFAULT, [
                'cost' => 10,
            ]);
        }

        $jobHandle->delete();

        echo 'Finished', "\n";
    }
}

/**
 * We can also manually check for graceful exit and do something about it like
 * commit current changes and queue a new job or rollback and reschedule current job.
 */
class GracefulExitIndexProjectCommitRunner implements \Zlikavac32\BeanstalkdLib\Runner
{

    /**
     * @var GracefulExit
     */
    private $gracefulExit;

    public function __construct(GracefulExit $gracefulExit)
    {
        $this->gracefulExit = $gracefulExit;
    }

    /**
     * @inheritdoc
     */
    public function run(JobHandle $jobHandle): void
    {
        $projectCommit = $jobHandle->payload();

        assert($projectCommit instanceof ProjectCommit);

        // simulate commit processing between 1 and 10 seconds
        $numberOfSecondsToWorkFor = crc32($projectCommit->commit()) % 10 + 1;
        $until = microtime(true) + $numberOfSecondsToWorkFor;

        echo 'Will run for ', $numberOfSecondsToWorkFor, ' seconds', "\n";

        while (microtime(true) < $until) {
            if ($this->gracefulExit->inProgress()) {
                echo 'Graceful exit in progress. Cleaning up', "\n";

                $jobHandle->release();

                return ;
            }

            password_hash(sha1((string)microtime(true)), PASSWORD_DEFAULT, [
                'cost' => 10,
            ]);
        }

        $jobHandle->delete();

        echo 'Finished', "\n";
    }
}

$tubeRunners = new \Ds\Map([
    TUBE_INDEX_PROJECT_COMMIT => new BuryOnExceptionRunner(
        new IndexProjectCommitRunner(), //or new GracefulExitIndexProjectCommitRunner($gracefulExit) for different implementation
        new ThrowNoneThrowableAuthority()
    ),
]);

// Job dispatcher is constructed with known tube runners and graceful exit object
$jobDispatcher = new \Zlikavac32\BeanstalkdLib\JobDispatcher\InterruptExceptionJobDispatcher(
    new TubeMapJobDispatcher($tubeRunners, $gracefulExit)
);

// Here we create signal handlers.
//
// Signal will not interrupt job execution, but will rather mark that
// graceful exit is in progress. That info is respected by the job dispatcher
// and protocol implementation.
//
// Additionally, we want to have a hard exit if second signal is caught
$interruptHandler = new CompositeInterruptHandler(
    $gracefulExit,
    new HardInterruptHandler()
);

// To install signal handlers, we can use provided SignalHandlerInstaller
$signalHandlerInstaller = new SignalHandlerInstaller($interruptHandler);

$signalHandlerInstaller->install();

// Job dispatcher is ran with a client and set of tube names that are watched
$jobDispatcher->run($client, new Set([TUBE_INDEX_PROJECT_COMMIT]));

// Remove install signal handlers
$signalHandlerInstaller->uninstall();