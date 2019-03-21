<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Client\TubeConfiguration;

use Zlikavac32\BeanstalkdLib\Serializer;

interface TubeConfiguration {

    public function defaultDelay(): int;

    public function defaultPriority(): int;

    public function defaultTimeToRun(): int;

    public function defaultTubePauseDelay(): int;

    public function serializer(): Serializer;
}
