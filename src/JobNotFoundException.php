<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class JobNotFoundException extends NotFoundException
{

    /**
     * @var int
     */
    private $jobId;

    public function __construct(int $jobId)
    {
        parent::__construct(\sprintf('Job %d not found on the server', $jobId));
        $this->jobId = $jobId;
    }

    public function jobId(): int
    {
        return $this->jobId;
    }
}
