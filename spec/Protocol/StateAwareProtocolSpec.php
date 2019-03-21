<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Protocol;

use Ds\Set;
use Ds\Vector;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\Protocol\StateAwareProtocol;

class StateAwareProtocolSpec extends ObjectBehavior {

    public function let(Protocol $protocol): void {
        $this->beConstructedWith($protocol);
    }

    public function it_is_initializable(): void {
        $this->shouldHaveType(StateAwareProtocol::class);
    }

    public function it_should_delegate_put(Protocol $protocol): void {
        $protocol->put(1, 2, 3, 'foo')
            ->willReturn(22);

        $this->put(1, 2, 3, 'foo')
            ->shouldReturn(22);
    }

    public function it_should_switch_tube_on_first_tube_use(Protocol $protocol): void {
        $protocol->useTube('foo')
            ->shouldBeCalled();

        $this->useTube('foo');
    }

    public function it_should_switch_tube_when_new_not_same_as_old(Protocol $protocol): void {
        $protocol->useTube('foo')
            ->shouldBeCalled();

        $this->useTube('foo');

        $protocol->useTube('bar')
            ->shouldBeCalled();

        $this->useTube('bar');
    }

    public function it_should_not_proxy_use_tube_if_new_tube_is_same_as_old(Protocol $protocol): void {
        $callCount = 0;

        $protocol->useTube('foo')
            ->will(
                function () use (&$callCount) {
                    $callCount++;
                }
            );

        $this->useTube('foo');
        $this->useTube('foo');

        if ($callCount > 1) {
            throw new FailureException(sprintf('Expected method "useTube" to be called only once'));
        }
    }

    public function it_should_proxy_reserve(Protocol $protocol): void {
        $job = new Job(32, '64');

        $protocol->reserve()
            ->willReturn($job);

        $this->reserve()
            ->shouldReturn($job);
    }

    public function it_should_proxy_reserve_with_timeout(Protocol $protocol): void {
        $job = new Job(32, '64');

        $protocol->reserveWithTimeout(128)
            ->willReturn($job);

        $this->reserveWithTimeout(128)
            ->shouldReturn($job);
    }

    public function it_should_proxy_delete(Protocol $protocol): void {
        $protocol->delete(32)
            ->shouldBeCalled();

        $this->delete(32);
    }

    public function it_should_proxy_release(Protocol $protocol): void {
        $protocol->release(32, 64, 128)
            ->shouldBeCalled();

        $this->release(32, 64, 128);
    }

    public function it_should_proxy_bury(Protocol $protocol): void {
        $protocol->bury(32, 64)
            ->shouldBeCalled();

        $this->bury(32, 64);
    }

    public function it_should_proxy_touch(Protocol $protocol): void {
        $protocol->touch(32)
            ->shouldBeCalled();

        $this->touch(32);
    }

    public function it_should_proxy_watch(Protocol $protocol): void {
        $protocol->watch('foo')
            ->willReturn(32);

        $this->watch('foo')
            ->shouldReturn(32);
    }

    public function it_should_proxy_ignore(Protocol $protocol): void {
        $protocol->ignore('foo')
            ->willReturn(32);

        $this->ignore('foo')
            ->shouldReturn(32);
    }

    public function it_should_proxy_peek(Protocol $protocol): void {
        $job = new Job(32, '64');

        $protocol->peek(32)
            ->willReturn($job);

        $this->peek(32)
            ->shouldReturn($job);
    }

    public function it_should_proxy_peek_ready(Protocol $protocol): void {
        $job = new Job(32, '64');

        $protocol->peekReady()
            ->willReturn($job);

        $this->peekReady()
            ->shouldReturn($job);
    }

    public function it_should_proxy_peek_delayed(Protocol $protocol): void {
        $job = new Job(32, '64');

        $protocol->peekDelayed()
            ->willReturn($job);

        $this->peekDelayed()
            ->shouldReturn($job);
    }

    public function it_should_proxy_peek_buried(Protocol $protocol): void {
        $job = new Job(32, '64');

        $protocol->peekBuried()
            ->willReturn($job);

        $this->peekBuried()
            ->shouldReturn($job);
    }

    public function it_should_proxy_kick(Protocol $protocol): void {
        $protocol->kick(32)
            ->willReturn(64);

        $this->kick(32)
            ->shouldReturn(64);
    }

    public function it_should_proxy_kick_job(Protocol $protocol): void {
        $protocol->kickJob(32)
            ->shouldBeCalled();

        $this->kickJob(32);
    }

    public function it_should_proxy_statsJob(Protocol $protocol): void {
        $payload = ['foo' => 1];

        $protocol->statsJob(32)
            ->willReturn($payload);

        $this->statsJob(32)
            ->shouldReturn($payload);
    }

    public function it_should_proxy_stats_tube(Protocol $protocol): void {
        $payload = ['bar' => 1];

        $protocol->statsTube('foo')
            ->willReturn($payload);

        $this->statsTube('foo')
            ->shouldReturn($payload);
    }

    public function it_should_proxy_stats(Protocol $protocol): void {
        $payload = ['foo' => 1];

        $protocol->stats()
            ->willReturn($payload);

        $this->stats()
            ->shouldReturn($payload);
    }

    public function it_should_proxy_list_tubes(Protocol $protocol): void {
        $tubesList = new Vector(['foo']);

        $protocol->listTubes()
            ->willReturn($tubesList);

        $this->listTubes()
            ->shouldReturn($tubesList);
    }

    public function it_should_proxy_list_tube_used(Protocol $protocol): void {
        $protocol->listTubeUsed()
            ->willReturn('foo');

        $this->listTubeUsed()
            ->shouldReturn('foo');
    }

    public function it_should_proxy_list_tubes_watched(Protocol $protocol): void {
        $tubesList = new Set(['foo']);

        $protocol->listTubesWatched()
            ->willReturn($tubesList);

        $this->listTubesWatched()
            ->shouldReturn($tubesList);
    }

    public function it_should_proxy_pauseTube(Protocol $protocol): void {
        $protocol->pauseTube('foo', 32)
            ->shouldBeCalled();

        $this->pauseTube('foo', 32);
    }
}
