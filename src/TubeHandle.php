<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Ds\Set;

interface TubeHandle
{

    public function tubeName(): string;

    /**
     * @throws BeanstalkdLibException
     */
    public function kick(int $numberOfJobs): int;

    /**
     * @param mixed $payload
     *
     * @throws JobBuriedException
     * @throws ExpectedCRLFException
     * @throws JobToBigException
     * @throws ServerInDrainingModeException
     * @throws BeanstalkdLibException
     */
    public function put($payload, ?int $priority = null, ?int $delay = null, ?int $timeToRun = null): JobHandle;

    /**
     * @throws TubeNotFoundException
     * @throws BeanstalkdLibException
     */
    public function stats(): TubeStats;

    /**
     * @throws TubeNotFoundException
     * @throws BeanstalkdLibException
     */
    public function pause(?int $delay = null): void;

    /**
     * @throws NotFoundException
     * @throws BeanstalkdLibException
     */
    public function peekReady(): JobHandle;

    /**
     * @throws NotFoundException
     * @throws BeanstalkdLibException
     */
    public function peekDelayed(): JobHandle;

    /**
     * @throws NotFoundException
     * @throws BeanstalkdLibException
     */
    public function peekBuried(): JobHandle;

    /**
     * @param Set|JobState[] $states
     *
     * @throws BeanstalkdLibException
     */
    public function flush(Set $states): void;
}
