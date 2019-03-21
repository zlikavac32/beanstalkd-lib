<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class TubeNotFoundException extends NotFoundException
{

    /**
     * @var string
     */
    private $tube;

    public function __construct(string $tube)
    {
        parent::__construct(\sprintf('Tube %s not found on the server', $tube));
        $this->tube = $tube;
    }

    public function tube(): string
    {
        return $this->tube;
    }
}
