<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\TestHelper\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Zlikavac32\BeanstalkdLib\JobNotFoundException;
use Zlikavac32\BeanstalkdLib\Protocol;

class JobIdExistsOnServer extends Constraint {

    /**
     * @var Protocol
     */
    private $protocol;

    public function __construct(Protocol $protocol) {
        parent::__construct();

        $this->protocol = $protocol;
    }

    protected function matches($other): bool {
        if (!is_int($other)) {
            return false;
        }

        try {
            $this->protocol->peek($other);
        } catch (JobNotFoundException $e) {
            return false;
        }

        return true;
    }

    protected function failureDescription($other): string {
        return sprintf('%s is job ID on server', $this->exporter->export($other));
    }

    public function toString(): string {
        return 'is valid job ID on server';
    }
}
