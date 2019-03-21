<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Ds\Map;
use Ds\Set;

interface Client
{

    /**
     * @return TubeHandle[]|Map
     *
     * @throws BeanstalkdLibException
     */
    public function tubes(): Map;

    /**
     * @throws BeanstalkdLibException
     */
    public function tube(string $tubeName): TubeHandle;

    /**
     * @throws BeanstalkdLibException
     */
    public function stats(): ServerStats;

    /**
     * @throws DeadlineSoonException
     * @throws BeanstalkdLibException
     */
    public function reserve(): JobHandle;

    /**
     * @throws JobNotFoundException
     * @throws BeanstalkdLibException
     */
    public function peek(int $jobId): JobHandle;

    /**
     * @throws ReserveTimedOutException
     * @throws DeadlineSoonException
     * @throws BeanstalkdLibException
     */
    public function reserveWithTimeout(int $timeout): JobHandle;

    /**
     * @return int Number of tubes currently watching
     * @throws BeanstalkdLibException
     */
    public function watch(string $tubeName): int;

    /**
     * @throws NotIgnoredException
     * @throws BeanstalkdLibException
     */
    public function ignoreDefaultTube(): int;

    /**
     * @throws NotIgnoredException
     * @throws BeanstalkdLibException
     */
    public function ignore(string $tubeName): int;

    /**
     * @return string[]|Set
     *
     * @throws BeanstalkdLibException
     */
    public function watchedTubeNames(): Set;
}
