<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class JobBuriedException extends ClientException
{

    /**
     * @var int
     */
    private $jobId;

    public function __construct(int $jobId)
    {
        parent::__construct(\sprintf('Newly created job %d buried', $jobId), null);
        $this->jobId = $jobId;
    }

    public function jobId(): int
    {
        return $this->jobId;
    }
}
