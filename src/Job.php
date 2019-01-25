<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class Job {

    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $payload;

    public function __construct(int $id, string $payload) {
        $this->id = $id;
        $this->payload = $payload;
    }

    public function id(): int {
        return $this->id;
    }

    public function payload(): string {
        return $this->payload;
    }
}
