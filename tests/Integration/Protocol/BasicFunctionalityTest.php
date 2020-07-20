<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Tests\Integration\Protocol;

use Ds\Set;
use PHPUnit\Framework\TestCase;
use Zlikavac32\BeanstalkdLib\JobState;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\ReserveTimedOutException;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\IsValidJobStatsArray;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\IsValidServerStatsArray;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\IsValidTubeStatsArray;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\JobIdExistsOnServer;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\JobIdHasDelay;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\JobIdHasPriority;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\JobIdIsInState;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\JobIsEqualTo;
use Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint\TwoTubeSetsMatch;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createDefaultProtocol;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createJob;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createJobInTube;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createJobWithDelay;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createJobWithPriority;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\createJobWithTimeToRun;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\purgeDefaultTube;
use function Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\purgeTube;

class BasicFunctionalityTest extends TestCase
{

    private ?Protocol $protocol;

    protected function setUp()
    {
        $this->protocol = createDefaultProtocol();

        $this->protocol->useTube('default');

        $this->protocol->watch('default');

        $existingTubes = new Set($this->protocol->listTubes());

        foreach ($existingTubes->diff(new Set(['default'])) as $tubeName) {
            $this->protocol->pauseTube($tubeName, 0);
            $this->protocol->ignore($tubeName);

            purgeTube($this->protocol, $tubeName);
        }

        $this->protocol->pauseTube('default', 0);

        purgeDefaultTube($this->protocol);
    }

    protected function tearDown()
    {
        $this->protocol = null;
    }

    /**
     * @test
     */
    public function job_can_be_placed_in_tube(): void
    {
        self::assertThat(
            $this->protocol->put(0, 0, 3600, 'foo'),
            new JobIdExistsOnServer($this->protocol)
        );
    }

    /**
     * @test
     */
    public function job_can_be_reserved(): void
    {
        $createdJob = createJob($this->protocol);

        self::assertThat($this->protocol->reserve(), new JobIsEqualTo($createdJob));
    }

    /**
     * @test
     */
    public function job_priority_is_respected(): void
    {
        $firstCreatedJob = createJobWithPriority($this->protocol, 5);
        $secondCreatedJob = createJobWithPriority($this->protocol, 2);

        self::assertThat($this->protocol->reserve(), new JobIsEqualTo($secondCreatedJob));
        self::assertThat($this->protocol->reserve(), new JobIsEqualTo($firstCreatedJob));
    }

    /**
     * @test
     */
    public function used_tube_can_be_retrieved(): void
    {
        $this->assertSame(
            'default',
            $this->protocol->listTubeUsed()
        );
    }

    /**
     * @test
     */
    public function job_delay_is_respected(): void
    {
        $createdJob = createJobWithDelay($this->protocol, 2);

        try {
            $this->protocol->reserveWithTimeout(1);

            $this->fail('Should not have anything to reserve due to the delay');
        } catch (ReserveTimedOutException $e) {
            // we're good here
        }

        sleep(2);

        self::assertThat($this->protocol->reserve(), new JobIsEqualTo($createdJob));
    }

    /**
     * @test
     */
    public function multiple_tubes_can_be_used(): void
    {
        $barTubeJob = createJobInTube($this->protocol, 'bar');
        $bazTubeJob = createJobInTube($this->protocol, 'baz');

        $this->protocol->watch('bar');
        $this->protocol->watch('baz');

        self::assertThat(
            $this->protocol->reserve(),
            new JobIsEqualTo($barTubeJob)
        );

        self::assertThat(
            $this->protocol->reserve(),
            new JobIsEqualTo($bazTubeJob)
        );
    }

    /**
     * @test
     */
    public function job_can_be_buried(): void
    {
        $createdJob = createJob($this->protocol);

        $reservedJob = $this->protocol->reserve();

        $this->protocol->bury($reservedJob->id(), 5);

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
    public function job_stats_can_be_retrieved(): void
    {
        $job = createJob($this->protocol);

        $this->assertThat(
            $this->protocol->statsJob($job->id()),
            new IsValidJobStatsArray()
        );
    }

    /**
     * @test
     */
    public function tube_stats_can_be_retrieved(): void
    {
        $this->assertThat(
            $this->protocol->statsTube('default'),
            new IsValidTubeStatsArray()
        );
    }

    /**
     * @test
     */
    public function server_stats_can_be_retrieved(): void
    {
        $this->assertThat(
            $this->protocol->stats(),
            new IsValidServerStatsArray()
        );
    }

    /**
     * @test
     */
    public function job_can_be_peeked(): void
    {
        $createdJob = createJob($this->protocol);

        $peekedJob = $this->protocol->peek($createdJob->id());

        self::assertThat(
            $peekedJob,
            new JobIsEqualTo($createdJob)
        );
    }

    /**
     * @test
     */
    public function ready_job_can_be_peeked(): void
    {
        $createdJob = createJob($this->protocol);

        $peekedJob = $this->protocol->peekReady();

        self::assertThat(
            $peekedJob,
            new JobIsEqualTo($createdJob)
        );
    }

    /**
     * @test
     */
    public function job_can_be_kicked(): void
    {
        $createdJob = createJob($this->protocol);

        $this->protocol->reserve();

        $this->protocol->bury($createdJob->id(), 2);

        $this->protocol->kickJob($createdJob->id());

        $reservedJob = $this->protocol->reserve();

        self::assertThat(
            $reservedJob,
            new JobIsEqualTo($createdJob)
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

        $this->protocol->reserve();

        $this->protocol->bury($firstCreatedJob->id(), 2);

        $this->protocol->reserve();

        $this->protocol->bury($secondCreatedJob->id(), 0);

        $this->protocol->kick(2);

        $firstReservedJob = $this->protocol->reserve();
        $this->protocol->delete($firstReservedJob->id());

        self::assertThat(
            $firstReservedJob,
            new JobIsEqualTo($secondCreatedJob)
        );

        $secondReservedJob = $this->protocol->reserve();

        self::assertThat(
            $secondReservedJob,
            new JobIsEqualTo($firstCreatedJob)
        );
    }

    /**
     * @test
     */
    public function job_can_be_released(): void
    {
        $createdJob = createJob($this->protocol);

        $this->protocol->reserve();
        $this->protocol->release($createdJob->id(), 2, 5);

        $peekedJob = $this->protocol->peekDelayed();

        self::assertThat(
            $peekedJob,
            new JobIsEqualTo($createdJob)
        );

        self::assertThat(
            $peekedJob->id(),
            new JobIdHasPriority($this->protocol, 2)
        );

        self::assertThat(
            $peekedJob->id(),
            new JobIdHasDelay($this->protocol, 5)
        );
    }

    /**
     * @test
     */
    public function delayed_job_can_be_peeked(): void
    {
        $createdJob = createJobWithDelay($this->protocol, 50);

        $peekedJob = $this->protocol->peekDelayed();

        self::assertThat(
            $peekedJob,
            new JobIsEqualTo($createdJob)
        );
    }

    /**
     * @test
     */
    public function buried_job_can_be_peeked(): void
    {
        $createdJob = createJob($this->protocol);

        $this->protocol->reserve();
        $this->protocol->bury($createdJob->id(), 0);

        $peekedJob = $this->protocol->peekBuried();

        self::assertThat(
            $peekedJob,
            new JobIsEqualTo($createdJob)
        );
    }

    /**
     * @test
     */
    public function watched_tubes_can_be_listed(): void
    {
        $this->protocol->watch('foo');
        $this->protocol->watch('bar');
        $this->protocol->watch('baz');

        $watched = $this->protocol->listTubesWatched();

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
        $this->protocol->watch('foo');
        $this->protocol->watch('bar');

        $existingTubes = $this->protocol->listTubes();

        self::assertThat(
            new Set($existingTubes),
            new TwoTubeSetsMatch(new Set(['default', 'foo', 'bar'])) // @todo: rename or split
        );
    }

    /**
     * @test
     */
    public function tube_can_be_paused(): void
    {
        $createdJob = createJob($this->protocol);

        $this->protocol->pauseTube('default', 10);

        try {
            $this->protocol->reserveWithTimeout(1);

            $this->fail('Expected to fail with reserve since tube is paused');
        } catch (ReserveTimedOutException $e) {
            // this is expected
        }

        $this->protocol->pauseTube('default', 0);

        $reservedJob = $this->protocol->reserveWithTimeout(1);

        self::assertThat(
            $reservedJob,
            new JobIsEqualTo($createdJob)
        );
    }

    /**
     * @test
     */
    public function long_running_job_can_be_touched(): void
    {
        $createdJob = createJobWithTimeToRun($this->protocol, 2);

        $reservedJob = $this->protocol->reserve();

        sleep(1);

        $this->protocol->touch($reservedJob->id());

        sleep(1);

        $this->protocol->bury($reservedJob->id(), 0);

        self::assertThat(
            $createdJob->id(),
            new JobIdIsInState($this->protocol, JobState::BURIED())
        );
    }

    /**
     * @test
     */
    public function job_with_zero_lenght_payload_can_be_read(): void
    {
        $createdJob = createJob($this->protocol, '');

        self::assertThat($this->protocol->reserve(), new JobIsEqualTo($createdJob));
    }
}
