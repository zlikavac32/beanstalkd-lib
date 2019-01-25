<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\phpSpec;

use PhpSpec\Exception\Example\FailureException;
use Zlikavac32\BeanstalkdLib\DefaultTubeHandle;

function assertSubjectIsValidTubeHandle($tubeHandle, string $tubeName): void {
    if (!$tubeHandle instanceof DefaultTubeHandle) {
        throw new FailureException(
            sprintf(
                'Tube handle for tube "%s" expected to be instance of %s',
                $tubeName,
                DefaultTubeHandle::class
            )
        );
    }

    if ($tubeHandle->tubeName() !== $tubeName) {
        throw new FailureException(
            sprintf(
                'Tube handle has name of "%s" while expected is "%s"',
                $tubeHandle->tubeName(),
                $tubeName
            )
        );
    }
}
