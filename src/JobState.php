<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Zlikavac32\Enum\Enum;

/**
 * @method static JobState READY
 * @method static JobState BURIED
 * @method static JobState DELAYED
 * @method static JobState RESERVED
 */
abstract class JobState extends Enum {

    protected static function enumerate(): array {
        return ['READY', 'BURIED', 'DELAYED', 'RESERVED'];
    }
}
