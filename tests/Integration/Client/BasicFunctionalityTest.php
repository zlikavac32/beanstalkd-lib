<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Tests\Integration\Client;

use Ds\Map;
use Ds\Set;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\TestCase;
use Zlikavac32\BeanstalkdLib\Adapter\PHP\Json\NativePHPJsonSerializer;
use Zlikavac32\BeanstalkdLib\Client;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\StaticTubeConfiguration;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeConfiguration;
use Zlikavac32\BeanstalkdLib\JobState;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\ReserveTimedOutException;
use Zlikavac32\BeanstalkdLib\Serializer;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\JobHandleIsForJob;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\JobIdExistsOnServer;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\JobIdHasDelay;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\JobIdHasPayload;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\JobIdHasPriority;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\JobIdIsInState;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\JobStatsIsForJob;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\ServerStatsIsForServer;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\TubeStatsIsForTube;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\TwoTubeSetsMatch;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createDefaultClient;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createDefaultProtocol;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createJob;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createJobInTube;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createJobWithDelay;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createJobWithPriority;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createJobWithTimeToRun;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createMockSerializer;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createMutableProxySerializer;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\purgeProtocol;

class BasicFunctionalityTest extends TestCase
{

    /**
     * @var Protocol
     */
    private $protocol;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var Map|TubeConfiguration[]
     */
    private $tubeConfigurations;
    /**
     * @var Serializer
     */
    private $serializer;

    protected function setUp()
    {
        $this->protocol = createDefaultProtocol();

        $mockSerializer = createMockSerializer();

        $this->serializer = createMutableProxySerializer($mockSerializer);

        $this->tubeConfigurations = new Map([
            'default' => new StaticTubeConfiguration(
                1, 2, 3, 4, $this->serializer
            ),
            'bar'     => new StaticTubeConfiguration(
                1, 2, 3, 4, $this->serializer
            ),
            'foo'     => new StaticTubeConfiguration(
                1, 2, 3, 4, $this->serializer
            ),
            'baz'     => new StaticTubeConfiguration(
                1, 2, 3, 4, $this->serializer
            ),
        ]);

        $this->client = createDefaultClient($this->protocol, $this->tubeConfigurations);

        purgeProtocol($this->protocol);
    }

    protected function tearDown()
    {
        unset($this->serializer);
        unset($this->tubeConfigurations);
        unset($this->client);
        unset($this->protocol);
    }

    /**
     * @test
     */
    public function job_can_be_placed_in_tube(): void
    {
        self::assertThat(
            $this->client->tube('default')
                ->put('some-payload')
                ->id(),
            new JobIdExistsOnServer($this->protocol)
        );
    }

    /**
     * @test
     */
    public function job_can_be_reserved(): void
    {
        $createdJob = createJob($this->protocol);

        self::assertThat($this->client->reserve(), new JobHandleIsForJob($createdJob));
    }

    /**
     * @test
     */
    public function job_priority_is_respected(): void
    {
        $firstCreatedJob = createJobWithPriority($this->protocol, 5);
        $secondCreatedJob = createJobWithPriority($this->protocol, 2);

        self::assertThat($this->client->reserve(), new JobHandleIsForJob($secondCreatedJob));
        self::assertThat($this->client->reserve(), new JobHandleIsForJob($firstCreatedJob));
    }

    /**
     * @test
     */
    public function job_delay_is_respected(): void
    {
        $createdJob = createJobWithDelay($this->protocol, 2);

        try {
            $this->client->reserveWithTimeout(1);

            $this->fail('Should not have anything to reserve due to the delay');
        } catch (ReserveTimedOutException $e) {
            // we're good here
        }

        sleep(2);

        self::assertThat($this->client->reserve(), new JobHandleIsForJob($createdJob));
    }

    /**
     * @test
     */
    public function multiple_tubes_can_be_used(): void
    {
        $barTubeJob = createJobInTube($this->protocol, 'bar');
        $bazTubeJob = createJobInTube($this->protocol, 'baz');

        $this->client->watch('bar');
        $this->client->watch('baz');

        self::assertThat(
            $this->client->reserve(),
            new JobHandleIsForJob($barTubeJob)
        );

        self::assertThat(
            $this->client->reserve(),
            new JobHandleIsForJob($bazTubeJob)
        );
    }

    /**
     * @test
     */
    public function job_can_be_buried(): void
    {
        $createdJob = createJob($this->protocol);

        $reservedJob = $this->client->reserve();

        $reservedJob->bury(5);

        self::assertThat(
            $createdJob->id(),
            new JobIdIsInState($this->protocol, JobState::BURIED())
        );

        self::assertThat(
            $createdJob->id(),
            new JobIdHasPriority($this->protocol, 5)
        );
    }

    /**
     * @test
     */
    public function job_can_be_buried_priority_from_tube_configraution(): void
    {
        $createdJob = createJob($this->protocol);

        $reservedJob = $this->client->reserve();

        $reservedJob->bury();

        self::assertThat(
            $createdJob->id(),
            new JobIdIsInState($this->protocol, JobState::BURIED())
        );

        self::assertThat(
            $createdJob->id(),
            new JobIdHasPriority($this->protocol, 2)
        );
    }

    /**
     * @test
     */
    public function job_can_be_peeked(): void
    {
        $job = createJob($this->protocol);

        $jobHandle = $this->client->peek($job->id());

        $this->assertThat(
            $jobHandle,
            new JobHandleIsForJob($job)
        );
    }

    /**
     * @test
     */
    public function job_stats_can_be_retrieved(): void
    {
        $job = createJob($this->protocol);

        $jobHandle = $this->client->peek($job->id());

        $this->assertThat(
            $jobHandle->stats(),
            new JobStatsIsForJob($this->protocol, $job)
        );
    }

    /**
     * @test
     */
    public function tube_stats_can_be_retrieved(): void
    {
        $this->assertThat(
            $this->client->tube('default')
                ->stats(),
            new TubeStatsIsForTube($this->protocol, 'default')
        );
    }

    /**
     * @test
     */
    public function server_stats_can_be_retrieved(): void
    {
        $this->assertThat(
            $this->client->stats(),
            new ServerStatsIsForServer($this->protocol)
        );
    }

    /**
     * @test
     */
    public function ready_job_can_be_peeked(): void
    {
        $createdJob = createJob($this->protocol);

        $peekedJob = $this->client->tube('default')
            ->peekReady();

        self::assertThat(
            $peekedJob,
            new JobHandleIsForJob($createdJob)
        );
    }

    /**
     * @test
     */
    public function job_can_be_kicked(): void
    {
        $createdJob = createJob($this->protocol);

        $jobHandle = $this->client->reserve();

        $jobHandle->bury();
        $jobHandle->kick();

        $reservedJob = $this->client->reserve();

        self::assertThat(
            $reservedJob,
            new JobHandleIsForJob($createdJob)
        );

        self::assertThat(
            $reservedJob->id(),
            new JobIdHasPriority($this->protocol, 2)
        );
    }

    /**
     * @test
     */
    public function multiple_jobs_can_be_kicked(): void
    {
        $firstCreatedJob = createJob($this->protocol);
        $secondCreatedJob = createJob($this->protocol);

        $this->client->reserve()
            ->bury(7);
        $this->client->reserve()
            ->bury(2);

        $this->client->tube('default')
            ->kick(2);

        $firstReservedJobHandle = $this->client->reserve();

        self::assertThat(
            $firstReservedJobHandle,
            new JobHandleIsForJob($secondCreatedJob)
        );

        $firstReservedJobHandle->delete();

        $secondReservedJobHandle = $this->client->reserve();

        self::assertThat(
            $secondReservedJobHandle,
            new JobHandleIsForJob($firstCreatedJob)
        );
    }

    /**
     * @test
     */
    public function job_can_be_released(): void
    {
        $createdJob = createJob($this->protocol);

        $this->client->reserve()
            ->release(9, 5);

        $peekedJobHandle = $this->client->tube('default')
            ->peekDelayed();

        self::assertThat(
            $peekedJobHandle,
            new JobHandleIsForJob($createdJob)
        );

        self::assertThat(
            $peekedJobHandle->id(),
            new JobIdHasPriority($this->protocol, 9)
        );

        self::assertThat(
            $peekedJobHandle->id(),
            new JobIdHasDelay($this->protocol, 5)
        );
    }

    /**
     * @test
     */
    public function job_can_be_released_with_default_values_from_tube_configuration(): void
    {
        $createdJob = createJob($this->protocol);

        $this->client->reserve()
            ->release();

        $peekedJobHandle = $this->client->tube('default')
            ->peekDelayed();

        self::assertThat(
            $peekedJobHandle,
            new JobHandleIsForJob($createdJob)
        );

        self::assertThat(
            $peekedJobHandle->id(),
            new JobIdHasPriority($this->protocol, 2)
        );

        self::assertThat(
            $peekedJobHandle->id(),
            new JobIdHasDelay($this->protocol, 1)
        );
    }

    /**
     * @test
     */
    public function buried_job_can_be_peeked(): void
    {
        $createdJob = createJob($this->protocol);

        $this->client->reserve()
            ->bury(8);

        $peekedJobHandle = $this->client->tube('default')
            ->peekBuried();

        self::assertThat(
            $peekedJobHandle,
            new JobHandleIsForJob($createdJob)
        );

        self::assertThat(
            $peekedJobHandle->id(),
            new JobIdHasPriority($this->protocol, 8)
        );
    }

    /**
     * @test
     */
    public function watched_tubes_can_be_listed(): void
    {
        $this->client->watch('foo');
        $this->client->watch('bar');
        $this->client->watch('baz');

        $watched = $this->client->watchedTubeNames();

        self::assertThat(
            $watched,
            new TwoTubeSetsMatch(new Set(['default', 'foo', 'bar', 'baz']))
        );
    }

    /**
     * @test
     */
    public function tubes_can_be_listed(): void
    {
        $this->client->watch('foo');
        $this->client->watch('bar');

        $existingTubes = $this->client->tubes();

        self::assertThat(
            $existingTubes->keys(),
            new TwoTubeSetsMatch(new Set(['default', 'foo', 'bar']))
        );
    }

    /**
     * @test
     */
    public function tube_can_be_paused(): void
    {
        $createdJob = createJob($this->protocol);

        $this->client->tube('default')
            ->pause(10);

        try {
            $this->protocol->reserveWithTimeout(1);

            $this->fail('Expected to fail with reserve since tube is paused');
        } catch (ReserveTimedOutException $e) {
            // this is expected
        }

        $this->protocol->pauseTube('default', 0);

        $reservedJobHandle = $this->client->reserveWithTimeout(1);

        self::assertThat(
            $reservedJobHandle,
            new JobHandleIsForJob($createdJob)
        );
    }

    /**
     * @test
     */
    public function long_running_job_can_be_touched(): void
    {
        createJobWithTimeToRun($this->protocol, 2);

        $reservedJobHandle = $this->client->reserve();

        sleep(1);

        $reservedJobHandle->touch();

        sleep(1);

        $reservedJobHandle->bury();

        self::assertThat(
            $reservedJobHandle->id(),
            new JobIdIsInState($this->protocol, JobState::BURIED())
        );
    }

    /**
     * @test
     */
    public function proper_serialization_should_be_performed(): void
    {
        $this->serializer->changeSerializerTo(new NativePHPJsonSerializer(true));

        $jobHandle = $this->client->tube('default')
            ->put([1, 2]);

        self::assertThat($jobHandle->id(), new JobIdHasPayload($this->protocol, '[1,2]'));

        $jobHandle = $this->client->peek($jobHandle->id());

        self::assertSame([1, 2], $jobHandle->payload());
    }

    /**
     * @test
     */
    public function single_tube_can_be_flushed(): void
    {
        $fooJob1 = createJobInTube($this->protocol, 'foo');
        $fooJob2 = createJobInTube($this->protocol, 'foo');
        $fooJob3 = createJobInTube($this->protocol, 'foo');
        $barJob1 = createJobInTube($this->protocol, 'bar');
        $barJob2 = createJobInTube($this->protocol, 'bar');

        $this->client->tube('foo')->flush();

        self::assertThat($fooJob1->id(), new LogicalNot(new JobIdExistsOnServer($this->protocol)));
        self::assertThat($fooJob2->id(), new LogicalNot(new JobIdExistsOnServer($this->protocol)));
        self::assertThat($fooJob3->id(), new LogicalNot(new JobIdExistsOnServer($this->protocol)));

        self::assertThat($barJob1->id(), new JobIdExistsOnServer($this->protocol));
        self::assertThat($barJob2->id(), new JobIdExistsOnServer($this->protocol));
    }

    /**
     * @test
     */
    public function all_tubes_can_be_flushed(): void
    {
        $fooJob1 = createJobInTube($this->protocol, 'foo');
        $fooJob2 = createJobInTube($this->protocol, 'foo');
        $fooJob3 = createJobInTube($this->protocol, 'foo');
        $barJob1 = createJobInTube($this->protocol, 'bar');
        $barJob2 = createJobInTube($this->protocol, 'bar');

        $this->client->flush();

        self::assertThat($fooJob1->id(), new LogicalNot(new JobIdExistsOnServer($this->protocol)));
        self::assertThat($fooJob2->id(), new LogicalNot(new JobIdExistsOnServer($this->protocol)));
        self::assertThat($fooJob3->id(), new LogicalNot(new JobIdExistsOnServer($this->protocol)));

        self::assertThat($barJob1->id(), new LogicalNot(new JobIdExistsOnServer($this->protocol)));
        self::assertThat($barJob2->id(), new LogicalNot(new JobIdExistsOnServer($this->protocol)));
    }
}
