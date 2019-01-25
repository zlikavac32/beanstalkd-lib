<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\Protocol;

class JobIdHasPayload extends Constraint {

    /**
     * @var Protocol
     */
    private $protocol;
    /**
     * @var int
     */
    private $payload;

    public function __construct(Protocol $protocol, string $payload) {
        parent::__construct();

        $this->protocol = $protocol;
        $this->payload = $payload;
    }

    protected function matches($other): bool {
        if (!is_int($other)) {
            return false;
        }

        return $this->payload === $this->protocol->peek($other)->payload();
    }

    protected function failureDescription($other): string {
        assert(is_int($other));

        return sprintf(
            'job %d has payload "%s" (actual payload "%s")',
            $other,
            $this->payload,
            $this->protocol->peek($other)->payload()
        );
    }

    public function toString(): string {
        return sprintf('job has payload "%s"', $this->payload);
    }
}
