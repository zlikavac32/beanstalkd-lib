<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Protocol;

use Ds\Map;
use Ds\Sequence;
use Ds\Set;
use Ds\Vector;
use LogicException;
use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\Protocol;

class TraceableProtocol implements Protocol
{

    /**
     * @var Protocol
     */
    private $protocol;
    /**
     * @var Sequence[]|array[]
     */
    private $traces;
    /**
     * @var int[][]|Map|Sequence[]
     */
    private $commandTraces;

    public function __construct(Protocol $protocol)
    {
        $this->protocol = $protocol;
        $this->traces = new Vector();
        $this->commandTraces = new Map();
    }

    public function put(int $priority, int $delay, int $timeToRun, string $payload): int
    {
        $this->addTrace('put',
            ['priority' => $priority, 'delay' => $delay, 'timeToRun' => $timeToRun, 'payload' => $payload]);

        return $this->protocol->put($priority, $delay, $timeToRun, $payload);
    }

    public function useTube(string $tube): void
    {
        $this->addTrace('useTube', ['tube' => $tube]);

        $this->protocol->useTube($tube);
    }

    public function reserve(): Job
    {
        $this->addTrace('reserve');

        return $this->protocol->reserve();
    }

    public function reserveWithTimeout(int $timeout): Job
    {
        $this->addTrace('reserveWithTimeout', ['timeout' => $timeout]);

        return $this->protocol->reserveWithTimeout($timeout);
    }

    public function delete(int $id): void
    {
        $this->addTrace('delete', ['id' => $id]);

        $this->protocol->delete($id);
    }

    public function release(int $id, int $priority, int $delay): void
    {
        $this->addTrace('release', ['id' => $id, 'priority' => $priority, 'delay' => $delay]);

        $this->protocol->release($id, $priority, $delay);
    }

    public function bury(int $id, int $priority): void
    {
        $this->addTrace('bury', ['id' => $id, 'priority' => $priority]);

        $this->protocol->bury($id, $priority);
    }

    public function touch(int $id): void
    {
        $this->addTrace('touch', ['id' => $id]);

        $this->protocol->touch($id);
    }

    public function watch(string $tube): int
    {
        $this->addTrace('watch', ['tube' => $tube]);

        return $this->protocol->watch($tube);
    }

    public function ignore(string $tube): int
    {
        $this->addTrace('ignore', ['tube' => $tube]);

        return $this->protocol->ignore($tube);
    }

    public function peek(int $id): Job
    {
        $this->addTrace('peek', ['id' => $id]);

        return $this->protocol->peek($id);
    }

    public function peekReady(): Job
    {
        $this->addTrace('peekReady');

        return $this->protocol->peekReady();
    }

    public function peekDelayed(): Job
    {
        $this->addTrace('peekDelayed');

        return $this->protocol->peekDelayed();
    }

    public function peekBuried(): Job
    {
        $this->addTrace('peekBuried');

        return $this->protocol->peekBuried();
    }

    public function kick(int $numberOfJobs): int
    {
        $this->addTrace('kick', ['numberOfJobs' => $numberOfJobs]);

        return $this->protocol->kick($numberOfJobs);
    }

    public function kickJob(int $id): void
    {
        $this->addTrace('kickJob', ['id' => $id]);

        $this->protocol->kickJob($id);
    }

    public function statsJob(int $id): array
    {
        $this->addTrace('statsJob', ['id' => $id]);

        return $this->protocol->statsJob($id);
    }

    public function statsTube(string $tube): array
    {
        $this->addTrace('statsTube', ['tube' => $tube]);

        return $this->protocol->statsTube($tube);
    }

    public function stats(): array
    {
        $this->addTrace('stats');

        return $this->protocol->stats();
    }

    public function listTubes(): Sequence
    {
        $this->addTrace('listTubes');

        return $this->protocol->listTubes();
    }

    public function listTubeUsed(): string
    {
        $this->addTrace('listTubeUsed');

        return $this->protocol->listTubeUsed();
    }

    public function listTubesWatched(): Set
    {
        $this->addTrace('listTubesWatched');

        return $this->protocol->listTubesWatched();
    }

    public function pauseTube(string $tube, int $delay): void
    {
        $this->addTrace('pauseTube', ['tube' => $tube, 'delay' => $delay]);

        $this->protocol->pauseTube($tube, $delay);
    }

    private function addTrace(string $command, array $arguments = []): void
    {
        $this->traces->push(['command' => $command, 'arguments' => $arguments]);

        if (!$this->commandTraces->hasKey($command)) {
            $this->commandTraces->put($command, new Vector());
        }

        $indexes = $this->commandTraces->get($command);
        assert($indexes instanceof Sequence);

        $indexes->push($this->traces->count() - 1);
    }

    /**
     * @return Sequence|array[]
     */
    public function traces(): Sequence
    {
        return $this->traces;
    }

    public function tracesExistForCommand(string $command): bool
    {
        return $this->commandTraces->hasKey($command);
    }

    /**
     * @return Sequence|array[]
     */
    public function tracesForCommand(string $command): Sequence
    {
        if (!$this->tracesExistForCommand($command)) {
            throw new LogicException(sprintf('No traces found for command %s. Perhaps you should call tracesExistForCommand() first?',
                $command));
        }

        $indexes = $this->commandTraces->get($command);
        assert($indexes instanceof Sequence);

        return $indexes->map(function (int $index): array {
            return $this->traces->get($index);
        });
    }

    public function clearTraces(): void
    {
        $this->traces->clear();
        $this->commandTraces->clear();
    }
}
