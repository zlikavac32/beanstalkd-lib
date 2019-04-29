<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Protocol;

use Ds\Sequence;
use Ds\Set;
use LogicException;
use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\Protocol\TraceableProtocol;
use Zlikavac32\BeanstalkdLib\Protocol\TraceableProtocol\Trace;

class TraceableProtocolSpec extends ObjectBehavior
{

    public function let(Protocol $protocol): void
    {
        $this->beConstructedWith($protocol);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(TraceableProtocol::class);
    }

    public function it_should_add_trace_for_put(Protocol $protocol): void
    {
        $protocol->put(1, 2, 3, 'foo')
            ->willReturn(32);

        $this->put(1, 2, 3, 'foo')
            ->shouldReturn(32);

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('put', [
                    'priority'  => 1,
                    'delay'     => 2,
                    'timeToRun' => 3,
                    'payload'   => 'foo',
                ]),
            ]);
    }

    public function it_should_add_trace_for_use_tube(Protocol $protocol): void
    {
        $protocol->useTube('foo')
            ->shouldBeCalled();

        $this->useTube('foo');

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('useTube', [
                    'tube' => 'foo',
                ]),
            ]);
    }

    public function it_should_add_trace_for_reserve(Protocol $protocol, Job $job): void
    {
        $protocol->reserve()
            ->willReturn($job);

        $this->reserve()
            ->shouldReturn($job);

        $this->traces()
            ->toArray()
            ->shouldBeLike([new Trace('reserve', [])]);
    }

    public function it_should_add_trace_for_reserve_with_timeout(Protocol $protocol, Job $job): void
    {
        $protocol->reserveWithTimeout(32)
            ->willReturn($job);

        $this->reserveWithTimeout(32)
            ->shouldReturn($job);

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('reserveWithTimeout', [
                    'timeout' => 32,
                ]),
            ]);
    }

    public function it_should_add_trace_for_delete(Protocol $protocol): void
    {
        $protocol->delete(32)
            ->shouldBeCalled();

        $this->delete(32);

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('delete', [
                    'id' => 32,
                ]),
            ]);
    }

    public function it_should_add_trace_for_release(Protocol $protocol): void
    {
        $protocol->release(32, 1, 2)
            ->shouldBeCalled();

        $this->release(32, 1, 2);

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('release', [
                    'id'       => 32,
                    'priority' => 1,
                    'delay'    => 2,
                ]),
            ]);
    }

    public function it_should_add_trace_for_bury(Protocol $protocol): void
    {
        $protocol->bury(32, 1)
            ->shouldBeCalled();

        $this->bury(32, 1);

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('bury', [
                    'id'       => 32,
                    'priority' => 1,
                ]),
            ]);
    }

    public function it_should_add_trace_for_touch(Protocol $protocol): void
    {
        $protocol->touch(32)
            ->shouldBeCalled();

        $this->touch(32);

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('touch', [
                    'id' => 32,
                ]),
            ]);
    }

    public function it_should_add_trace_for_watch(Protocol $protocol): void
    {
        $protocol->watch('foo')
            ->willReturn(32);

        $this->watch('foo')
            ->shouldReturn(32);

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('watch', [
                    'tube' => 'foo',
                ]),
            ]);
    }

    public function it_should_add_trace_for_ignore(Protocol $protocol): void
    {
        $protocol->ignore('foo')
            ->willReturn(32);

        $this->ignore('foo')
            ->shouldReturn(32);

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('ignore', [
                    'tube' => 'foo',
                ]),
            ]);
    }

    public function it_should_add_trace_for_peek(Protocol $protocol, Job $job): void
    {
        $protocol->peek(32)
            ->willReturn($job);

        $this->peek(32)
            ->shouldReturn($job);

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('peek', [
                    'id' => 32,
                ]),
            ]);
    }

    public function it_should_add_trace_for_peek_ready(Protocol $protocol, Job $job): void
    {
        $protocol->peekReady()
            ->willReturn($job);

        $this->peekReady()
            ->shouldReturn($job);

        $this->traces()
            ->toArray()
            ->shouldBeLike([new Trace('peekReady', [])]);
    }

    public function it_should_add_trace_for_peek_delayed(Protocol $protocol, Job $job): void
    {
        $protocol->peekDelayed()
            ->willReturn($job);

        $this->peekDelayed()
            ->shouldReturn($job);

        $this->traces()
            ->toArray()
            ->shouldBeLike([new Trace('peekDelayed', [])]);
    }

    public function it_should_add_trace_for_peek_buried(Protocol $protocol, Job $job): void
    {
        $protocol->peekBuried()
            ->willReturn($job);

        $this->peekBuried()
            ->shouldReturn($job);

        $this->traces()
            ->toArray()
            ->shouldBeLike([new Trace('peekBuried', [])]);
    }

    public function it_should_add_trace_for_kick(Protocol $protocol): void
    {
        $protocol->kick(32)
            ->shouldBeCalled();

        $this->kick(32);

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('kick', [
                    'numberOfJobs' => 32,
                ]),
            ]);
    }

    public function it_should_add_trace_for_stats_job(Protocol $protocol): void
    {
        $protocol->statsJob(32)
            ->willReturn([1]);

        $this->statsJob(32)
            ->shouldReturn([1]);

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('statsJob', [
                    'id' => 32,
                ]),
            ]);
    }

    public function it_should_add_trace_for_stats_tube(Protocol $protocol): void
    {
        $protocol->statsTube('foo')
            ->willReturn([1]);

        $this->statsTube('foo')
            ->shouldReturn([1]);

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('statsTube', [
                    'tube' => 'foo',
                ]),
            ]);
    }

    public function it_should_add_trace_for_stats(Protocol $protocol): void
    {
        $protocol->stats()
            ->willReturn([1]);

        $this->stats()
            ->shouldReturn([1]);

        $this->traces()
            ->toArray()
            ->shouldBeLike([new Trace('stats', [])]);
    }

    public function it_should_add_trace_for_list_tubes(Protocol $protocol, Sequence $sequence): void
    {
        $protocol->listTubes()
            ->willReturn($sequence);

        $this->listTubes()
            ->shouldReturn($sequence);

        $this->traces()
            ->toArray()
            ->shouldBeLike([new Trace('listTubes', [])]);
    }

    public function it_should_add_trace_for_list_tube_used(Protocol $protocol): void
    {
        $protocol->listTubeUsed()
            ->willReturn('foo');

        $this->listTubeUsed()
            ->shouldReturn('foo');

        $this->traces()
            ->toArray()
            ->shouldBeLike([new Trace('listTubeUsed', [])]);
    }

    public function it_should_add_trace_for_list_tubes_watched(Protocol $protocol): void
    {
        $set = new Set();

        $protocol->listTubesWatched()
            ->willReturn($set);

        $this->listTubesWatched()
            ->shouldReturn($set);

        $this->traces()
            ->toArray()
            ->shouldBeLike([new Trace('listTubesWatched', [])]);
    }

    public function it_should_add_trace_for_pause_tube(Protocol $protocol): void
    {
        $protocol->pauseTube('foo', 32)
            ->shouldBeCalled();

        $this->pauseTube('foo', 32);

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('pauseTube', [
                    'tube'  => 'foo',
                    'delay' => 32,
                ]),
            ]);
    }

    public function it_should_have_trace(Protocol $protocol, Sequence $sequence): void
    {
        $protocol->stats()
            ->willReturn([]);

        $this->stats();

        $this->tracesExistForCommand('stats')
            ->shouldReturn(true);
        $this->tracesExistForCommand('listTubes')
            ->shouldReturn(false);
    }

    public function it_should_return_multiple_traces(Protocol $protocol, Sequence $sequence): void
    {
        $protocol->stats()
            ->willReturn([]);
        $protocol->listTubes()
            ->willReturn($sequence);

        $this->stats();
        $this->listTubes();
        $this->stats();

        $this->traces()
            ->toArray()
            ->shouldBeLike([
                new Trace('stats', []),
                new Trace('listTubes', []),
                new Trace('stats', []),
            ]);
    }

    public function it_should_have_traces_for_command(Protocol $protocol, Sequence $sequence): void
    {
        $protocol->stats()
            ->willReturn([]);
        $protocol->listTubes()
            ->willReturn($sequence);

        $this->stats();
        $this->listTubes();
        $this->stats();

        $this->tracesForCommand('stats')
            ->toArray()
            ->shouldBeLike([
                new Trace('stats', []),
                new Trace('stats', []),
            ]);

        $this->tracesForCommand('listTubes')
            ->toArray()
            ->shouldBeLike([
                new Trace('listTubes', []),
            ]);
    }

    public function it_should_throw_exception_when_traces_dont_exist_for_command(): void
    {
        $this->shouldThrow(new LogicException('No traces found for command stats. Perhaps you should call tracesExistForCommand() first?'))
            ->duringTracesForCommand('stats');
    }
}
