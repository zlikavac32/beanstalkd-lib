<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

/**
 * Used to serialize/deserialize job payload.
 */
interface Serializer {

    /**
     * @param mixed $payload
     *
     * @throws SerializeException
     */
    public function serialize($payload): string;

    /**
     * @return mixed
     *
     * @throws DeserializeException
     */
    public function deserialize(string $payload);
}
