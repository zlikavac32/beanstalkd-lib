<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Adapter\PHPUnit\Constraint;

use Ds\Sequence;
use PHPUnit\Framework\Constraint\Constraint;
use Zlikavac32\BeanstalkdLib\Protocol\TraceableProtocol;
use Zlikavac32\BeanstalkdLib\Protocol\TraceableProtocol\Trace;

class OrderedTracesExistInProtocol extends Constraint
{

    /**
     * @var Sequence|Trace[]
     */
    private $traces;

    /**
     * @var Sequence|Trace[] $traces
     */
    public function __construct(Sequence $traces)
    {
        parent::__construct();

        assert($traces->count() > 0);

        $this->traces = $traces;
    }

    protected function matches($other): bool
    {
        assert($other instanceof TraceableProtocol);

        $traces = $other->traces();

        for ($i = 0, $tracesLen = $traces->count() - $this->traces->count() + 1; $i < $tracesLen; $i++) {
            foreach ($this->traces as $j => $expectingTrace) {
                if (!$expectingTrace->equals($traces[$i + $j])) {
                    continue 2;
                }
            }

            return true;
        }

        return false;
    }

    protected function failureDescription($other): string
    {
        assert($other instanceof TraceableProtocol);

        return sprintf(
            '[%s] contains ordered list of traces [%s]',
            $this->mapTraces($other->traces()),
            $this->mapTraces($this->traces)
        );
    }

    public function toString(): string
    {
        return sprintf('contains traces [%s] in order', $this->mapTraces($this->traces));
    }

    private function mapTraces(Sequence $traces): string
    {
        return $traces->map(function (Trace $trace): string {
            return (string) $trace;
        })
            ->join(', ');
    }
}
