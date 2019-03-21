<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use Zlikavac32\Enum\Enum;

/**
 * @method static Command PUT
 * @method static Command PEEK
 * @method static Command PEEK_READY
 * @method static Command PEEK_DELAYED
 * @method static Command PEEK_BURIED
 * @method static Command RESERVE
 * @method static Command USE
 * @method static Command WATCH
 * @method static Command IGNORE
 * @method static Command DELETE
 * @method static Command RELEASE
 * @method static Command BURY
 * @method static Command KICK
 * @method static Command STATS
 * @method static Command STATS_JOB
 * @method static Command STATS_TUBE
 * @method static Command LIST_TUBES
 * @method static Command LIST_TUBE_USED
 * @method static Command LIST_TUBES_WATCHED
 * @method static Command PAUSE_TUBE
 */
abstract class Command extends Enum
{

    protected static function enumerate(): array
    {
        return [
            'PUT',
            'PEEK',
            'PEEK_READY',
            'PEEK_DELAYED',
            'PEEK_BURIED',
            'RESERVE',
            'USE',
            'WATCH',
            'IGNORE',
            'DELETE',
            'RELEASE',
            'BURY',
            'KICK',
            'STATS',
            'STATS_JOB',
            'STATS_TUBE',
            'LIST_TUBES',
            'LIST_TUBE_USED',
            'LIST_TUBES_WATCHED',
            'PAUSE_TUBE',
        ];
    }
}
