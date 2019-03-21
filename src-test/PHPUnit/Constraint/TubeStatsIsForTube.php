<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\TubeStats;

class TubeStatsIsForTube extends Constraint
{

    /**
     * @var Protocol
     */
    private $protocol;
    /**
     * @var string
     */
    private $tubeName;

    public function __construct(Protocol $protocol, string $tubeName)
    {
        parent::__construct();

        $this->protocol = $protocol;
        $this->tubeName = $tubeName;
    }

    protected function matches($other): bool
    {
        if (!$other instanceof TubeStats) {
            return false;
        }

        return $other->tubeName() === $this->tubeName;
    }

    protected function failureDescription($other): string
    {
        return sprintf(
            '%s is tube stats for %s',
            $this->exporter->export($other),
            $this->tubeName
        );
    }

    public function toString(): string
    {
        return sprintf('is tube stats for %s', $this->tubeName);
    }
}
