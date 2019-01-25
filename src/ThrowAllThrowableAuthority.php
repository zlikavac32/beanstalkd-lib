<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Throwable;

class ThrowAllThrowableAuthority implements ThrowableAuthority {

    public function shouldRethrow(Throwable $e): bool {
        return true;
    }
}
