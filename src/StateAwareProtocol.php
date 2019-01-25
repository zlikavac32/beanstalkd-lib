<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Ds\Sequence;
use Ds\Set;

/**
 * Keeps track of currently used tube and eliminates redundant calls to useTube().
 */
class StateAwareProtocol implements Protocol {

    /**
     * @var Protocol
     */
    private $protocol;

    /**
     * @var string
     */
    private $currentTube = null;

    public function __construct(Protocol $protocol) {
        $this->protocol = $protocol;
    }

    /**
     * @inheritdoc
     */
    public function put(int $priority, int $delay, int $timeToRun, string $payload): int {
        return $this->protocol->put($priority, $delay, $timeToRun, $payload);
    }

    /**
     * @inheritdoc
     */
    public function useTube(string $tube): void {
        if ($this->currentTube === $tube) {
            return;
        }

        $this->protocol->useTube($tube);

        $this->currentTube = $tube;
    }

    /**
     * @inheritdoc
     */
    public function reserve(): Job {
        return $this->protocol->reserve();
    }

    /**
     * @inheritdoc
     */
    public function reserveWithTimeout(int $timeout): Job {
        return $this->protocol->reserveWithTimeout($timeout);
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id): void {
        $this->protocol->delete($id);
    }

    /**
     * @inheritdoc
     */
    public function release(int $id, int $priority, int $delay): void {
        $this->protocol->release($id, $priority, $delay);
    }

    /**
     * @inheritdoc
     */
    public function bury(int $id, int $priority): void {
        $this->protocol->bury($id, $priority);
    }

    /**
     * @inheritdoc
     */
    public function touch(int $id): void {
        $this->protocol->touch($id);
    }

    /**
     * @inheritdoc
     */
    public function watch(string $tube): int {
        return $this->protocol->watch($tube);
    }

    /**
     * @inheritdoc
     */
    public function ignore(string $tube): int {
        return $this->protocol->ignore($tube);
    }

    /**
     * @inheritdoc
     */
    public function peek(int $id): Job {
        return $this->protocol->peek($id);
    }

    /**
     * @inheritdoc
     */
    public function peekReady(): Job {
        return $this->protocol->peekReady();
    }

    /**
     * @inheritdoc
     */
    public function peekDelayed(): Job {
        return $this->protocol->peekDelayed();
    }

    /**
     * @inheritdoc
     */
    public function peekBuried(): Job {
        return $this->protocol->peekBuried();
    }

    /**
     * @inheritdoc
     */
    public function kick(int $numberOfJobs): int {
        return $this->protocol->kick($numberOfJobs);
    }

    /**
     * @inheritdoc
     */
    public function kickJob(int $id): void {
        $this->protocol->kickJob($id);
    }

    /**
     * @inheritdoc
     */
    public function statsJob(int $id): array {
        return $this->protocol->statsJob($id);
    }

    /**
     * @inheritdoc
     */
    public function statsTube(string $tube): array {
        return $this->protocol->statsTube($tube);
    }

    /**
     * @inheritdoc
     */
    public function stats(): array {
        return $this->protocol->stats();
    }

    /**
     * @inheritdoc
     */
    public function listTubes(): Sequence {
        return $this->protocol->listTubes();
    }

    /**
     * @inheritdoc
     */
    public function listTubeUsed(): string {
        return $this->protocol->listTubeUsed();
    }

    /**
     * @inheritdoc
     */
    public function listTubesWatched(): Set {
        return $this->protocol->listTubesWatched();
    }

    /**
     * @inheritdoc
     */
    public function pauseTube(string $tube, int $delay): void {
        $this->protocol->pauseTube($tube, $delay);
    }
}
