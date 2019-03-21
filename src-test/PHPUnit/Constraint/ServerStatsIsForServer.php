<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\ServerStats;

class ServerStatsIsForServer extends Constraint
{

    /**
     * @var Protocol
     */
    private $protocol;

    public function __construct(Protocol $protocol)
    {
        parent::__construct();

        $this->protocol = $protocol;
    }

    protected function matches($other): bool
    {
        return $other instanceof ServerStats;
    }

    protected function failureDescription($other): string
    {
        return sprintf(
            '%s is server stats',
            $this->exporter->export($other)
        );
    }

    public function toString(): string
    {
        return 'is server stats';
    }
}
