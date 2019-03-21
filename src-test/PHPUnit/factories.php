<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit;

use Throwable;
use Zlikavac32\AlarmScheduler\AlarmHandler;
use Zlikavac32\AlarmScheduler\AlarmScheduler;
use Zlikavac32\BeanstalkdLib\Adapter\PHP\Socket\NativePHPSocket;
use Zlikavac32\BeanstalkdLib\Adapter\Symfony\Yaml\SymfonyYamlParser;
use Zlikavac32\BeanstalkdLib\Client;
use Zlikavac32\BeanstalkdLib\Client\DefaultClient;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeConfigurationFactory;
use Zlikavac32\BeanstalkdLib\GracefulExit;
use Zlikavac32\BeanstalkdLib\InterruptHandler;
use Zlikavac32\BeanstalkdLib\InterruptHandler\CompositeInterruptHandler;
use Zlikavac32\BeanstalkdLib\InterruptHandler\HardInterruptHandler;
use Zlikavac32\BeanstalkdLib\InterruptHandler\TimeoutHardInterruptHandler;
use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\Protocol\ProtocolOverSocket;
use Zlikavac32\BeanstalkdLib\Protocol\StateAwareProtocol;
use Zlikavac32\BeanstalkdLib\Runner;
use Zlikavac32\BeanstalkdLib\Runner\AutoTouchRunner;
use Zlikavac32\BeanstalkdLib\Runner\BuryOnExceptionRunner;
use Zlikavac32\BeanstalkdLib\Runner\ReleaseOnInterruptExceptionRunner;
use Zlikavac32\BeanstalkdLib\Runner\ThrowableAuthority;
use Zlikavac32\BeanstalkdLib\Serializer;
use Zlikavac32\BeanstalkdLib\Socket\ExclusiveAccessSocket;

function createDefaultProtocol(int $readTimeout = 1500000): Protocol
{
    $socket = new ExclusiveAccessSocket(
        new NativePHPSocket($readTimeout)
    );

    $yamlParser = new SymfonyYamlParser();

    $gracefulExit = new class implements GracefulExit
    {

        public function inProgress(): bool
        {
            return true;
        }
    };

    return new StateAwareProtocol(
        new ProtocolOverSocket($socket->open(hostIpFromEnv(), hostPortFromEnv()), $gracefulExit, $yamlParser)
    );
}

function createMockSerializer(): Serializer
{
    return new class implements Serializer
    {

        public function serialize($payload): string
        {
            return $payload;
        }

        public function deserialize(string $payload)
        {
            return $payload;
        }
    };
}

function createMutableProxySerializer(Serializer $serializer): Serializer
{
    return new class($serializer) implements Serializer
    {

        /**
         * @var Serializer
         */
        private $serializer;

        public function __construct(Serializer $serializer)
        {
            $this->serializer = $serializer;
        }

        public function serialize($payload): string
        {
            return $this->serializer->serialize($payload);
        }

        public function deserialize(string $payload)
        {
            return $this->serializer->deserialize($payload);
        }

        public function changeSerializerTo(Serializer $serializer): void
        {
            $this->serializer = $serializer;
        }
    };
}

function createDefaultInterruptHandler(
    InterruptHandler $gracefulExitInterruptHandler,
    AlarmScheduler $alarmScheduler,
    AlarmHandler $alarmHandler,
    int $timeout = 3
): InterruptHandler {
    return new CompositeInterruptHandler(
        new TimeoutHardInterruptHandler($alarmScheduler, $alarmHandler, $timeout),
        new HardInterruptHandler(),
        $gracefulExitInterruptHandler
    );
}

function createDefaultRunner(Runner $runner, Client $client, AlarmScheduler $alarmScheduler): Runner
{
    return new ReleaseOnInterruptExceptionRunner(
        new BuryOnExceptionRunner(
            new AutoTouchRunner(
                $runner,
                $client,
                $alarmScheduler
            ),
            new class implements ThrowableAuthority
            {

                public function shouldRethrow(Throwable $e): bool
                {
                    return true;
                }
            }
        )
    );
}

function createMutableProxyRunner(Runner $runner): Runner
{
    return new class($runner) implements Runner
    {

        /**
         * @var Runner
         */
        private $runner;

        public function __construct(Runner $runner)
        {
            $this->runner = $runner;
        }

        public function run(JobHandle $jobHandle): void
        {
            $this->runner->run($jobHandle);
        }

        public function changeRunnerTo(Runner $runner): void
        {
            $this->runner = $runner;
        }
    };
}

function createRunnerThatSleepsAndThenBuriesJob(int $sleepTime): Runner
{
    return new class($sleepTime) implements Runner
    {

        /**
         * @var int
         */
        private $sleepTime;

        public function __construct(int $sleepTime)
        {
            $this->sleepTime = $sleepTime;
        }

        public function run(JobHandle $jobHandle): void
        {
            sleepWithoutInterrupt($this->sleepTime);

            $jobHandle->bury();
        }
    };
}

function createJustBuryJobRunner(): Runner
{
    return new class implements Runner
    {

        public function run(JobHandle $jobHandle): void
        {
            $jobHandle->bury();
        }
    };
}

function createDefaultClient(Protocol $protocol, TubeConfigurationFactory $tubeConfigurationFactory): Client
{
    return new DefaultClient($protocol, $tubeConfigurationFactory);
}

function createJobInTube(Protocol $protocol, string $tube): Job
{
    return createJob($protocol, 'foo', 1024, 0, 60, $tube);
}

function createJobWithPriority(Protocol $protocol, int $priority): Job
{
    return createJob($protocol, 'foo', $priority);
}

function createJobWithDelay(Protocol $protocol, int $delay): Job
{
    return createJob($protocol, 'foo', 1024, $delay);
}

function createJobWithTimeToRun(Protocol $protocol, int $timeToRun): Job
{
    return createJob($protocol, 'foo', 1024, 0, $timeToRun);
}

function createJob(
    Protocol $protocol,
    string $payload = 'foo',
    $priority = 1024,
    $delay = 0,
    $timeToRun = 60,
    string $tube = 'default'
): Job {
    $protocol->useTube($tube);

    $jobId = $protocol->put($priority, $delay, $timeToRun, $payload);

    return new Job($jobId, $payload);
}
