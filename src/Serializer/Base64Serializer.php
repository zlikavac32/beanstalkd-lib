<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Serializer;

use Zlikavac32\BeanstalkdLib\DeserializeException;
use Zlikavac32\BeanstalkdLib\Serializer;

class Base64Serializer implements Serializer
{

    public function serialize($payload): string
    {
        assert(is_string($payload));

        return base64_encode($payload);
    }

    public function deserialize(string $payload): string
    {
        $decoded = base64_decode($payload, true);

        if (false === $decoded) {
            throw new DeserializeException('Unable to perform base64_decode', $payload);
        }

        return $decoded;
    }
}
