<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class NotIgnoredException extends ClientException {

    /**
     * @var string
     */
    private $tube;

    public function __construct(string $tube) {
        parent::__construct(\sprintf('Tube %s could not be ignored', $tube), null);
        $this->tube = $tube;
    }

    public function tube(): string {
        return $this->tube;
    }
}
