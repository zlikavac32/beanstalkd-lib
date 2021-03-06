<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Tests\Integration\JobDispatcher;

use Ds\Map;
use Ds\Set;
use PHPUnit\Framework\TestCase;
use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\AlarmScheduler\NaiveAlarmScheduler;
use Zlikavac32\BeanstalkdLib\Client;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\StaticTubeConfiguration;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeConfiguration;
use Zlikavac32\BeanstalkdLib\GracefulExit;
use Zlikavac32\BeanstalkdLib\InterruptException;
use Zlikavac32\BeanstalkdLib\InterruptHandler\GracefulExitInterruptHandler;
use Zlikavac32\BeanstalkdLib\JobDispatcher;
use Zlikavac32\BeanstalkdLib\JobDispatcher\InterruptExceptionJobDispatcher;
use Zlikavac32\BeanstalkdLib\JobDispatcher\TubeMapJobDispatcher;
use Zlikavac32\BeanstalkdLib\JobState;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\Runner;
use Zlikavac32\BeanstalkdLib\Serializer;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\JobIdIsInState;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createDefaultClient;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createDefaultInterruptHandler;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createDefaultProtocol;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createDefaultRunner;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createJob;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createJobInTube;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createJustBuryJobRunner;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createMockSerializer;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createMutableProxyRunner;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createMutableProxySerializer;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createRunnerThatSleepsAndThenBuriesJob;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\purgeProtocol;

class BasicFunctionalityTest extends TestCase
{

    private ?Protocol $protocol;

    private ?Client $client;
    /**
     * @var TubeConfiguration[]
     */
    private ?Map $tubeConfigurations;

    private ?Serializer $serializer;

    private ?JobDispatcher $jobDispatcher;

    private ?AlarmScheduler $alarmScheduler;

    private ?Runner $barTubeRunner;

    private ?Map $tubeRunners;

    private ?GracefulExit $gracefulExit;

    private ?AlarmHandler $emulateInterruptAlarmHandler;

    private static $previousAsyncSignals;

    private static $previousSignalHandler;

    public static function setUpBeforeClass(): void
    {
        self::$previousAsyncSignals = pcntl_async_signals(true);
        self::$previousSignalHandler = pcntl_signal_get_handler(SIGUSR2);
    }

    public static function tearDownAfterClass(): void
    {
        pcntl_async_signals(self::$previousAsyncSignals);
        pcntl_signal(SIGUSR2, self::$previousSignalHandler);
    }

    protected function setUp()
    {
        $this->alarmScheduler = new NaiveAlarmScheduler();
        $this->alarmScheduler->start();

        $this->emulateInterruptAlarmHandler = new class implements AlarmHandler
        {

            public function handle(AlarmScheduler $scheduler): void
            {
                posix_kill(getmypid(), SIGUSR2);
            }
        };

        $this->protocol = createDefaultProtocol();

        $mockSerializer = createMockSerializer();

        $this->serializer = createMutableProxySerializer($mockSerializer);

        $this->tubeConfigurations = new Map([
            'bar' => new StaticTubeConfiguration(
                1, 2, 3, 4, $this->serializer
            ),
        ]);

        $this->client = createDefaultClient($this->protocol, $this->tubeConfigurations);

        purgeProtocol($this->protocol);

        $this->barTubeRunner = createMutableProxyRunner(createJustBuryJobRunner());

        $this->tubeRunners = new Map(
            [
                'bar' => createDefaultRunner(
                    $this->barTubeRunner,
                    $this->client,
                    $this->alarmScheduler
                ),
            ]
        );

        $this->gracefulExit = new GracefulExitInterruptHandler();

        $this->jobDispatcher = new InterruptExceptionJobDispatcher(
            new TubeMapJobDispatcher($this->tubeRunners, $this->gracefulExit)
        );

        $interruptHandler = createDefaultInterruptHandler(
            $this->gracefulExit,
            $this->alarmScheduler,
            $this->jobDispatcher
        );

        pcntl_signal(
            SIGUSR2,
            function () use ($interruptHandler): void {
                $interruptHandler->handle();
            }
        );
    }

    protected function tearDown()
    {
        pcntl_signal(SIGUSR2, SIG_IGN);

        $this->alarmScheduler->finish();

        $this->jobDispatcher = null;
        $this->alarmScheduler = null;
        $this->barTubeRunner = null;
        $this->tubeRunners = null;
        $this->gracefulExit = null;
        $this->emulateInterruptAlarmHandler = null;
        $this->serializer = null;
        $this->tubeConfigurations = null;
        $this->client = null;
        $this->protocol = null;
    }

    /**
     * @test
     */
    public function log_running_job_is_auto_touched(): void
    {
        $createdJob = createJob($this->protocol, 'foo', 1024, 0, 6, 'bar');

        $this->barTubeRunner->changeRunnerTo(createRunnerThatSleepsAndThenBuriesJob(6));

        $this->jobDispatcher->run($this->client, new Set(['bar']), 1);

        self::assertThat($createdJob->id(), new JobIdIsInState($this->protocol, JobState::BURIED()));
    }

    /**
     * @test
     */
    public function runner_should_exit_gracefully_after_interrupt_is_caught(): void
    {
        $firstCreatedJob = createJobInTube($this->protocol, 'bar');
        $secondCreatedJob = createJobInTube($this->protocol, 'bar');

        $this->barTubeRunner->changeRunnerTo(createRunnerThatSleepsAndThenBuriesJob(2));

        $this->alarmScheduler->schedule(1, $this->emulateInterruptAlarmHandler);

        $this->jobDispatcher->run($this->client, new Set(['bar']), 3);

        self::assertThat($firstCreatedJob->id(), new JobIdIsInState($this->protocol, JobState::BURIED()));
        self::assertThat($secondCreatedJob->id(), new JobIdIsInState($this->protocol, JobState::READY()));
    }

    /**
     * @expectedException \Zlikavac32\BeanstalkdLib\InterruptException
     * @test
     */
    public function runner_should_perform_hard_interrupt_on_second_signal(): void
    {
        $createdJob = createJobInTube($this->protocol, 'bar');

        $this->barTubeRunner->changeRunnerTo(createRunnerThatSleepsAndThenBuriesJob(4));

        $this->alarmScheduler->schedule(1, $this->emulateInterruptAlarmHandler);
        $this->alarmScheduler->schedule(2, $this->emulateInterruptAlarmHandler);

        try {
            $this->jobDispatcher->run($this->client, new Set(['bar']), 1);
        } catch (InterruptException $e) {
            self::assertThat($createdJob->id(), new JobIdIsInState($this->protocol, JobState::DELAYED()));

            throw $e;
        }
    }

    /**
     * @expectedException \Zlikavac32\BeanstalkdLib\InterruptException
     * @test
     */
    public function runner_should_perform_delayed_hard_interrupt(): void
    {
        $createdJob = createJobInTube($this->protocol, 'bar');

        $this->barTubeRunner->changeRunnerTo(createRunnerThatSleepsAndThenBuriesJob(8));

        $this->alarmScheduler->schedule(1, $this->emulateInterruptAlarmHandler);

        try {
            $this->jobDispatcher->run($this->client, new Set(['bar']), 1);
        } catch (InterruptException $e) {
            self::assertThat($createdJob->id(), new JobIdIsInState($this->protocol, JobState::DELAYED()));

            throw $e;
        }
    }
}
