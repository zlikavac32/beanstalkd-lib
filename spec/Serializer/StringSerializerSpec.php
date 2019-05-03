<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Serializer;

use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\Serializer\StringSerializer;

class StringSerializerSpec extends ObjectBehavior
{

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(StringSerializer::class);
    }

    public function it_should_serialize_string(): void
    {
        $this->serialize('foo-bar')
            ->shouldReturn('foo-bar');
    }

    public function it_should_deserialize_string(): void
    {
        $this->deserialize('foo-bar')
            ->shouldReturn('foo-bar');
    }
}
