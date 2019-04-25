<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\phpSpec;

use PhpSpec\Exception\Example\FailureException;
use Zlikavac32\BeanstalkdLib\Client\ProtocolTubeHandle;

function assertSubjectIsValidTubeHandle($tubeHandle, string $tubeName): void
{
    if (!$tubeHandle instanceof ProtocolTubeHandle) {
        throw new FailureException(
            sprintf(
                'Tube handle for tube "%s" expected to be instance of %s',
                $tubeName,
                ProtocolTubeHandle::class
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
