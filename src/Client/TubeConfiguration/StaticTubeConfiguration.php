<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Client\TubeConfiguration;

use Zlikavac32\BeanstalkdLib\Serializer;

class StaticTubeConfiguration implements TubeConfiguration {

    /**
     * @var int
     */
    private $defaultDelay;
    /**
     * @var int
     */
    private $defaultPriority;
    /**
     * @var int
     */
    private $defaultTimeToRun;
    /**
     * @var int
     */
    private $defaultTubePauseDelay;
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        int $defaultDelay,
        int $defaultPriority,
        int $defaultTimeToRun,
        int $defaultTubePauseDelay,
        Serializer $serializer
    ) {
        $this->defaultDelay = $defaultDelay;
        $this->defaultPriority = $defaultPriority;
        $this->defaultTimeToRun = $defaultTimeToRun;
        $this->defaultTubePauseDelay = $defaultTubePauseDelay;
        $this->serializer = $serializer;
    }

    public function defaultDelay(): int {
        return $this->defaultDelay;
    }

    public function defaultPriority(): int {
        return $this->defaultPriority;
    }

    public function defaultTimeToRun(): int {
        return $this->defaultTimeToRun;
    }

    public function defaultTubePauseDelay(): int {
        return $this->defaultTubePauseDelay;
    }

    public function serializer(): Serializer {
        return $this->serializer;
    }
}
