<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class ServerStats
{

    /**
     * @var string
     */
    private $hostname;
    /**
     * @var string
     */
    private $version;
    /**
     * @var int
     */
    private $processId;
    /**
     * @var int
     */
    private $upTime;
    /**
     * @var int
     */
    private $maxJobSize;
    /**
     * @var float
     */
    private $cpuUserTime;
    /**
     * @var float
     */
    private $cpuSystemTime;
    /**
     * @var ServerMetrics
     */
    private $serverMetrics;
    /**
     * @var CommandMetrics
     */
    private $commandMetrics;

    public function __construct(
        string $hostname,
        string $version,
        int $processId,
        int $upTime,
        int $maxJobSize,
        float $cpuUserTime,
        float $cpuSystemTime,
        ServerMetrics $serverMetrics,
        CommandMetrics $commandMetrics
    ) {
        $this->hostname = $hostname;
        $this->version = $version;
        $this->processId = $processId;
        $this->upTime = $upTime;
        $this->maxJobSize = $maxJobSize;
        $this->cpuUserTime = $cpuUserTime;
        $this->cpuSystemTime = $cpuSystemTime;
        $this->serverMetrics = $serverMetrics;
        $this->commandMetrics = $commandMetrics;
    }

    public function hostname(): string
    {
        return $this->hostname;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function processId(): int
    {
        return $this->processId;
    }

    public function upTime(): int
    {
        return $this->upTime;
    }

    public function maxJobSize(): int
    {
        return $this->maxJobSize;
    }

    public function cpuUserTime(): float
    {
        return $this->cpuUserTime;
    }

    public function cpuSystemTime(): float
    {
        return $this->cpuSystemTime;
    }

    public function serverMetrics(): ServerMetrics
    {
        return $this->serverMetrics;
    }

    public function commandMetrics(): CommandMetrics
    {
        return $this->commandMetrics;
    }
}
