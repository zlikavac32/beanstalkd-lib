<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

interface ProtocolTubePurger {

    public function purge(Protocol $protocol, string ...$tubes): void;
}
