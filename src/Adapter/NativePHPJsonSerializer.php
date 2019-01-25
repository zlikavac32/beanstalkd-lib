<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Adapter;

use JsonException;
use Zlikavac32\BeanstalkdLib\DeserializeException;
use Zlikavac32\BeanstalkdLib\SerializeException;
use Zlikavac32\BeanstalkdLib\Serializer;

class NativePHPJsonSerializer implements Serializer {

    /**
     * @var int
     */
    private $encodeOptions;
    /**
     * @var int
     */
    private $decodeOptions;
    /**
     * @var bool
     */
    private $decodeObjectAsArray;
    /**
     * @var int
     */
    private $decodeDepth;

    public function __construct(
        bool $objectAsArray,
        int $encodeOptions = \JSON_PRESERVE_ZERO_FRACTION,
        int $decodeOptions = 0,
        int $decodeDepth = 512
    ) {
        $this->encodeOptions = $encodeOptions | \JSON_THROW_ON_ERROR;
        $this->decodeOptions = $decodeOptions | \JSON_THROW_ON_ERROR;
        $this->decodeObjectAsArray = $objectAsArray;
        $this->decodeDepth = $decodeDepth;
    }

    public function serialize($payload): string {
        try {
            return \json_encode($payload, $this->encodeOptions);
        } catch (JsonException $e) {
            throw new SerializeException('There was an error while serializing as JSON', $payload, $e);
        }
    }

    public function deserialize(string $payload) {
        try {
            return \json_decode($payload, $this->decodeObjectAsArray, $this->decodeDepth, $this->decodeOptions);
        } catch (JsonException $e) {
            throw new DeserializeException('There was an error while deserializing JSON', $payload, $e);
        }
    }
}
