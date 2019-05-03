<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Serializer;

use Zlikavac32\BeanstalkdLib\Serializer;

class StringSerializer implements Serializer
{

    public function serialize($payload): string
    {
        assert(is_string($payload));

        return $payload;
    }

    public function deserialize(string $payload): string
    {
        return $payload;
    }
}
