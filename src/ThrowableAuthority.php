<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Throwable;

interface ThrowableAuthority {

    public function shouldRethrow(Throwable $e): bool;
}
