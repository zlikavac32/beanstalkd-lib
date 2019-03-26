<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Zlikavac32\Enum\Enum;

/**
 * @method static ServerErrorCause OUT_OF_MEMORY
 * @method static ServerErrorCause INTERNAL_ERROR
 */
abstract class ServerErrorCause extends Enum
{

}
