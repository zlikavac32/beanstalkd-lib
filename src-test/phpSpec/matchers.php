<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\phpSpec;

use Ds\Map;
use Ds\Set;
use PhpSpec\Exception\Example\FailureException;
use Zlikavac32\BeanstalkdLib\Client\DefaultJobHandle;
use Zlikavac32\BeanstalkdLib\Client\DefaultTubeHandle;

function beJobHandleFor($subject, int $jobId, $payload): bool
{
    if (!$subject instanceof DefaultJobHandle) {
        throw new FailureException(sprintf('Expected instance of %s', DefaultTubeHandle::class));
    }

    if ($subject->id() !== $jobId) {
        throw new FailureException(sprintf('Expected job ID to be %d but got %d', $jobId, $subject->id()));
    }

    if ($subject->payload() !== $payload) {
        throw new FailureException('Payload mismatch');
    }

    return true;
}

;

function beTubeHandleFor($subject, string $tubeName): bool
{
    assertSubjectIsValidTubeHandle($subject, $tubeName);

    return true;
}

function beMapOfTubes($subject, Set $expectedTubes): bool
{
    if (!$subject instanceof Map) {
        throw new FailureException(sprintf('Expected instance of %s', Map::class));
    }

    foreach ($expectedTubes as $tubeName) {
        if (!$subject->hasKey($tubeName)) {
            throw new FailureException(sprintf('Key "%s" not found in map', $tubeName));
        }

        assertSubjectIsValidTubeHandle($subject->get($tubeName), $tubeName);
    }

    return true;
}
