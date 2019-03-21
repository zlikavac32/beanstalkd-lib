<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

use RuntimeException;
use Throwable;

class YamlParseException extends RuntimeException
{

    /**
     * @var string
     */
    private $causingContent;

    public function __construct(string $message, string $causingContent, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->causingContent = $causingContent;
    }

    public function causingContent(): string
    {
        return $this->causingContent;
    }
}
