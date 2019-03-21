<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Adapter\PHP\Json;

use PhpSpec\ObjectBehavior;
use stdClass;
use Zlikavac32\BeanstalkdLib\Adapter\PHP\Json\NativePHPJsonSerializer;
use Zlikavac32\BeanstalkdLib\DeserializeException;
use Zlikavac32\BeanstalkdLib\SerializeException;

class NativePHPJsonSerializerSpec extends ObjectBehavior {

    public function let(): void {
        $this->beConstructedWith(true);
    }

    public function it_is_initializable(): void {
        $this->shouldHaveType(NativePHPJsonSerializer::class);
    }

    public function it_should_throw_error_on_serialize_error(): void {
        $resource = \fopen('php://memory', 'r');

        $payload = [$resource];

        try {
            $this->shouldThrow(
                new SerializeException(
                    'Type is not supported',
                    $payload
                )
            )
                ->duringSerialize($payload);
        } finally {
            fclose($resource);
        }
    }

    public function it_should_throw_error_on_deserialize_error(): void {
        $payload = '[';
        $this->shouldThrow(
            new DeserializeException('Syntax error', $payload)
        )
            ->duringDeserialize($payload);
    }

    public function it_should_decode_object_as_array(): void {
        $this->deserialize('{}')
            ->shouldReturn([]);
    }

    public function it_should_decode_object_as_object(): void {
        $this->beConstructedWith(false);
        $this->deserialize('{}')
            ->shouldBeLike(new stdClass());
    }

    public function it_should_allow_changing_of_encode_options(): void {
        $this->beConstructedWith(true, JSON_PRETTY_PRINT);

        $this->serialize(['foo' => 1])
            ->shouldReturn(
                <<<'JSON'
{
    "foo": 1
}
JSON
            );
    }

    public function it_should_allow_changing_of_decode_options(): void {
        $this->beConstructedWith(true, 0, JSON_BIGINT_AS_STRING);

        $this->deserialize('12345678912345767891234')
            ->shouldReturn('12345678912345767891234');
    }
}
