<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Tests\Unit\Adapter\PHPUnit\Constraint;

use Ds\Sequence;
use Ds\Vector;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Zlikavac32\BeanstalkdLib\Adapter\PHPUnit\Constraint\OrderedTracesExistInProtocol;
use Zlikavac32\BeanstalkdLib\Protocol\TraceableProtocol;
use Zlikavac32\BeanstalkdLib\Protocol\TraceableProtocol\Trace;

class OrderedTracesExistInProtocolTest extends TestCase
{

    /**
     * @test
     */
    public function to_string_output_is_correct(): void
    {
        $constraint = new OrderedTracesExistInProtocol(new Vector([new Trace('foo', ['bar' => 'baz'])]));

        self::assertSame("contains traces [{foo, {'bar': 'baz'}}] in order", $constraint->toString());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function no_exception_is_thrown_when_traces_match_exactly(): void
    {
        $constraint = new OrderedTracesExistInProtocol(new Vector([new Trace('foo', ['bar' => 'baz'])]));

        $constraint->evaluate(new MockTraceableProtocol(new Vector([new Trace('foo', ['bar' => 'baz'])])));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function no_exception_is_thrown_when_traces_match_later_in_sequence(): void
    {
        $constraint = new OrderedTracesExistInProtocol(new Vector([
            new Trace('foo', ['bar' => 'baz']),
            new Trace('demo', ['bar' => 'baz']),
        ]));

        $constraint->evaluate(new MockTraceableProtocol(new Vector([
            new Trace('foo', ['bar' => 'not-right']),
            new Trace('foo', ['bar' => 'baz']),
            new Trace('demo', ['bar' => 'baz']),
        ])));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function no_exception_is_thrown_when_traces_match_partially_first_and_then_fully_later_in_sequence(): void
    {
        $constraint = new OrderedTracesExistInProtocol(new Vector([
            new Trace('foo', ['bar' => 'baz']),
            new Trace('demo', ['bar' => 'baz']),
        ]));

        $constraint->evaluate(new MockTraceableProtocol(new Vector([
            new Trace('foo', ['bar' => 'not-right']),
            new Trace('foo', ['bar' => 'baz']),
            new Trace('foo', ['not-bar' => 'not-baz']),
            new Trace('foo', ['bar' => 'baz']),
            new Trace('demo', ['bar' => 'baz']),
        ])));
    }

    /**
     * @test
     */
    public function it_should_fail_when_traces_is_empty(): void
    {
        $constraint = new OrderedTracesExistInProtocol(new Vector([
            new Trace('foo', ['bar' => 'baz']),
            new Trace('demo', ['bar' => 'baz']),
        ]));

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Failed asserting that [] contains ordered list of traces [{foo, {\'bar\': \'baz\'}}, {demo, {\'bar\': \'baz\'}}].');

        $constraint->evaluate(new MockTraceableProtocol(new Vector([])));
    }

    /**
     * @test
     */
    public function it_should_fail_when_traces_not_found(): void
    {
        $constraint = new OrderedTracesExistInProtocol(new Vector([
            new Trace('foo', ['bar' => 'baz']),
            new Trace('demo', ['bar' => 'baz']),
        ]));

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Failed asserting that [{foo, {\'bar\': \'not-right\'}}, {foo, {\'not-bar\': \'not-baz\'}}] contains ordered list of traces [{foo, {\'bar\': \'baz\'}}, {demo, {\'bar\': \'baz\'}}].');

        $constraint->evaluate(new MockTraceableProtocol(new Vector([
            new Trace('foo', ['bar' => 'not-right']),
            new Trace('foo', ['not-bar' => 'not-baz']),
        ])));
    }
}

class MockTraceableProtocol extends TraceableProtocol
{

    /**
     * @var Sequence
     */
    private $traces;

    public function __construct(Sequence $traces)
    {
        $this->traces = $traces;
    }

    public function traces(): Sequence
    {
        return $this->traces;
    }
}
