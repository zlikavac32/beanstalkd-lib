<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Adapter\PHP\Json;

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
        $this->encodeOptions = $encodeOptions;
        $this->decodeOptions = $decodeOptions;
        $this->decodeObjectAsArray = $objectAsArray;
        $this->decodeDepth = $decodeDepth;
    }

    public function serialize($payload): string {
        try {
            return \json_encode($payload, $this->encodeOptions);
        } finally {
            if (\json_last_error() !== JSON_ERROR_NONE) {
                throw new SerializeException(\json_last_error_msg(), $payload);
            }
        }
    }

    public function deserialize(string $payload) {
        try {
            return \json_decode($payload, $this->decodeObjectAsArray, $this->decodeDepth, $this->decodeOptions);
        } finally {
            if (\json_last_error() !== JSON_ERROR_NONE) {
                throw new DeserializeException(\json_last_error_msg(), $payload);
            }
        }
    }
}
