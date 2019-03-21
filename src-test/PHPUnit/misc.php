<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit;

use Ds\Set;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\ProtocolTubePurger\DefaultProtocolTubePurger;

function purgeTube(Protocol $protocol, string $tubeName): void
{
    (new DefaultProtocolTubePurger())->purge($protocol, $tubeName);
}

function purgeDefaultTube(Protocol $protocol): void
{
    purgeTube($protocol, 'default');
}

function purgeProtocol(Protocol $protocol): void
{
    $protocol->useTube('default');

    $protocol->watch('default');

    $existingTubes = new Set($protocol->listTubes());

    foreach ($existingTubes->diff(new Set(['default'])) as $tubeName) {
        $protocol->pauseTube($tubeName, 0);
        $protocol->ignore($tubeName);

        purgeTube($protocol, $tubeName);
    }

    $protocol->pauseTube('default', 0);

    purgeDefaultTube($protocol);
}

// works on linux (http://man7.org/linux/man-pages/man3/sleep.3.html#notes)
function sleepWithoutInterrupt(int $sleep): void
{
    $now = microtime(true);
    $end = $now + $sleep;

    do {
        sleep($sleep);
        $now = microtime(true);

        $sleep = max(1, (int)floor($end - $now));
    } while ($now < $end);
}

function hostIpFromEnv(): string
{
    assertEnvExists('BEANSTALKD_HOST');

    return $_ENV['BEANSTALKD_HOST'];
}

function hostPortFromEnv(): int
{
    assertEnvExists('BEANSTALKD_PORT');

    return (int)$_ENV['BEANSTALKD_PORT'];
}
