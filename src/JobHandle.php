<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

interface JobHandle {

    public function id(): int;

    public function payload();

    /**
     * @throws JobNotFoundException
     * @throws BeanstalkdLibException
     */
    public function kick(): void;

    /**
     * @throws JobNotFoundException
     * @throws BeanstalkdLibException
     */
    public function stats(): JobStats;

    /**
     * @throws JobNotFoundException
     * @throws BeanstalkdLibException
     */
    public function delete(): void;

    /**
     * @throws JobBuriedException
     * @throws JobNotFoundException
     * @throws BeanstalkdLibException
     */
    public function release(?int $priority = null, ?int $delay = null): void;

    /**
     * @throws JobNotFoundException
     * @throws BeanstalkdLibException
     */
    public function bury(?int $priority = null): void;

    /**
     * @throws JobNotFoundException
     * @throws BeanstalkdLibException
     */
    public function touch(): void;
}
