<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Ds\Sequence;
use Ds\Set;

interface Protocol
{

    /**
     * @return int
     *
     * @throws JobBuriedException
     * @throws ExpectedCRLFException
     * @throws ServerInDrainingModeException
     * @throws BeanstalkdLibException
     */
    public function put(int $priority, int $delay, int $timeToRun, string $payload): int;

    /**
     * @throws BeanstalkdLibException
     */
    public function useTube(string $tube): void;

    /**
     * @throws DeadlineSoonException
     * @throws BeanstalkdLibException
     */
    public function reserve(): Job;

    /**
     * @throws DeadlineSoonException
     * @throws ReserveTimedOutException
     * @throws BeanstalkdLibException
     */
    public function reserveWithTimeout(int $timeout): Job;

    /**
     * @throws JobNotFoundException
     * @throws BeanstalkdLibException
     */
    public function delete(int $id): void;

    /**
     * @throws JobBuriedException
     * @throws JobNotFoundException
     * @throws BeanstalkdLibException
     */
    public function release(int $id, int $priority, int $delay): void;

    /**
     * @throws JobNotFoundException
     * @throws BeanstalkdLibException
     */
    public function bury(int $id, int $priority): void;

    /**
     * @throws JobNotFoundException
     * @throws BeanstalkdLibException
     */
    public function touch(int $id): void;

    /**
     * @throws BeanstalkdLibException
     */
    public function watch(string $tube): int;

    /**
     * @throws BeanstalkdLibException
     */
    public function ignore(string $tube): int;

    /**
     * @throws JobNotFoundException
     * @throws BeanstalkdLibException
     */
    public function peek(int $id): Job;

    /**
     * @throws NotFoundException
     * @throws BeanstalkdLibException
     */
    public function peekReady(): Job;

    /**
     * @throws NotFoundException
     * @throws BeanstalkdLibException
     */
    public function peekDelayed(): Job;

    /**
     * @throws NotFoundException
     * @throws BeanstalkdLibException
     */
    public function peekBuried(): Job;

    /**
     * @throws BeanstalkdLibException
     */
    public function kick(int $numberOfJobs): int;

    /**
     * @throws JobNotFoundException
     * @throws BeanstalkdLibException
     */
    public function kickJob(int $id): void;

    /**
     * @throws NotFoundException
     * @throws BeanstalkdLibException
     */
    public function statsJob(int $id): array;

    /**
     * @throws TubeNotFoundException
     * @throws BeanstalkdLibException
     */
    public function statsTube(string $tube): array;

    /**
     * @throws BeanstalkdLibException
     */
    public function stats(): array;

    /**
     * @return Sequence|string[]
     *
     * @throws BeanstalkdLibException
     */
    public function listTubes(): Sequence;

    /**
     * @throws BeanstalkdLibException
     */
    public function listTubeUsed(): string;

    /**
     * @return Set|string[]
     *
     * @throws BeanstalkdLibException
     */
    public function listTubesWatched(): Set;

    /**
     * @throws TubeNotFoundException
     * @throws BeanstalkdLibException
     */
    public function pauseTube(string $tube, int $delay): void;
}
