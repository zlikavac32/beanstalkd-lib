<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Protocol\TraceableProtocol;

use PhpSpec\ObjectBehavior;
use stdClass;
use Zlikavac32\BeanstalkdLib\Protocol\TraceableProtocol\Trace;

class TraceSpec extends ObjectBehavior
{

    public function let(): void
    {
        $this->beConstructedWith('foo', ['bar' => 'baz']);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Trace::class);
    }

    public function it_should_return_correct_method(): void
    {
        $this->method()
            ->shouldReturn('foo');
    }

    public function it_should_return_correct_arguments(): void
    {
        $this->arguments()
            ->shouldReturn(['bar' => 'baz']);
    }

    public function it_should_return_correct_hash(): void
    {
        $this->hash()
            ->shouldReturn('5c4b2a91ce37c20cafc491db68e760c9e3220bb6');
    }

    public function it_should_equal_to_an_instance(): void
    {
        $this->equals(new Trace('foo', ['bar' => 'baz']))
            ->shouldReturn(true);
    }

    public function it_should_equal_to_an_instance_of_some_other_class(): void
    {
        $this->equals(new stdClass())
            ->shouldReturn(false);
    }

    public function it_should_equal_to_an_instance_when_method_does_not_match(): void
    {
        $this->equals(new Trace('demo', ['bar' => 'baz']))
            ->shouldReturn(false);
    }

    public function it_should_equal_to_an_instance_when_arguments_do_not_match(): void
    {
        $this->equals(new Trace('foo', ['bar' => 'not-baz']))
            ->shouldReturn(false);
    }

    public function it_should_return_correct_simple_case_string(): void
    {
        $this->__toString()
            ->shouldReturn('{foo, {\'bar\': \'baz\'}}');
    }

    public function it_should_return_correct_to_string_for_indexed_array(): void
    {
        $this->beConstructedWith('foo', ['bar' => [1, 2, 3]]);

        $this->__toString()
            ->shouldReturn('{foo, {\'bar\': [1, 2, 3]}}');
    }

    public function it_should_return_correct_to_string_for_associative_array(): void
    {
        $this->beConstructedWith('foo', ['bar' => ['a' => 1, 'b' => 2, 'c' => 3]]);

        $this->__toString()
            ->shouldReturn('{foo, {\'bar\': {\'a\': 1, \'b\': 2, \'c\': 3}}}');
    }

    public function it_should_return_correct_to_string_for_recursive_array(): void
    {
        $this->beConstructedWith('foo', ['bar' => [[1], [2], [3]]]);

        $this->__toString()
            ->shouldReturn('{foo, {\'bar\': [[1], [2], [3]]}}');
    }

    public function it_should_return_correct_to_string_for_object_with_to_string(): void
    {
        $this->beConstructedWith('foo', [
            'bar' => new class extends stdClass
            {

                public function __toString(): string
                {
                    return 'to-string';
                }
            },
        ]);

        $this->__toString()
            ->shouldReturn('{foo, {\'bar\': \'to-string\'}}');
    }

    public function it_should_return_correct_to_string_for_object_without_to_string(): void
    {
        $this->beConstructedWith('foo', ['bar' => new stdClass()]);

        $this->__toString()
            ->shouldReturn('{foo, {\'bar\': (instance of stdClass)}}');
    }
}
