<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Serializer;

use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\DeserializeException;
use Zlikavac32\BeanstalkdLib\Serializer\Base64Serializer;

class Base64SerializerSpec extends ObjectBehavior
{

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Base64Serializer::class);
    }

    public function it_should_serialize_string(): void
    {
        $this->serialize('foo-bar')
            ->shouldReturn('Zm9vLWJhcg==');
    }

    public function it_should_deserialize_string(): void
    {
        $this->deserialize('Zm9vLWJhcg==')
            ->shouldReturn('foo-bar');
    }

    public function it_should_throw_exception_when_payload_not_base64_payload(): void
    {
        $this->shouldThrow(new DeserializeException('Unable to perform base64_decode', '!'))
            ->duringDeserialize('!');
    }
}
