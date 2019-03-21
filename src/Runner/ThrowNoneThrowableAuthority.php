<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Runner;

use Throwable;

class ThrowNoneThrowableAuthority implements ThrowableAuthority {

    public function shouldRethrow(Throwable $e): bool {
        return false;
    }
}
