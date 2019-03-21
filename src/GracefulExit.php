<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

interface GracefulExit
{

    public function inProgress(): bool;
}
