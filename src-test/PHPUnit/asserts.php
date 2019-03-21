<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit;

use LogicException;

function assertEnvExists(string $key): void
{
    if (isset($_ENV[$key])) {
        return;
    }

    throw new LogicException('Missing BEANSTALKD_HOST');
}
