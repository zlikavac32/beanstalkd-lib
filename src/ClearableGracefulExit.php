<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

interface ClearableGracefulExit extends GracefulExit {

    public function clear(): void;
}
