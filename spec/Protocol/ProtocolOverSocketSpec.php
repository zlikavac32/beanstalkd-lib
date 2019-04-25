<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Protocol;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Zlikavac32\BeanstalkdLib\BeanstalkdLibException;
use Zlikavac32\BeanstalkdLib\DeadlineSoonException;
use Zlikavac32\BeanstalkdLib\ExpectedCRLFException;
use Zlikavac32\BeanstalkdLib\GracefulExit;
use Zlikavac32\BeanstalkdLib\InterruptedCallSocketException;
use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\JobBuriedException;
use Zlikavac32\BeanstalkdLib\JobNotFoundException;
use Zlikavac32\BeanstalkdLib\JobToBigException;
use Zlikavac32\BeanstalkdLib\NotFoundException;
use Zlikavac32\BeanstalkdLib\NotIgnoredException;
use Zlikavac32\BeanstalkdLib\Protocol\ProtocolOverSocket;
use Zlikavac32\BeanstalkdLib\ReserveInterruptedException;
use Zlikavac32\BeanstalkdLib\ReserveTimedOutException;
use Zlikavac32\BeanstalkdLib\ServerInDrainingModeException;
use Zlikavac32\BeanstalkdLib\SocketException;
use Zlikavac32\BeanstalkdLib\SocketHandle;
use Zlikavac32\BeanstalkdLib\TryAgainSocketException;
use Zlikavac32\BeanstalkdLib\TubeNotFoundException;
use Zlikavac32\BeanstalkdLib\YamlParseException;
use Zlikavac32\BeanstalkdLib\YamlParser;

class ProtocolOverSocketSpec extends ObjectBehavior
{

    public function let(SocketHandle $socketHandle, GracefulExit $gracefulExit, YamlParser $yamlParser): void
    {
        $this->beConstructedWith($socketHandle, $gracefulExit, $yamlParser);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProtocolOverSocket::class);
    }

    public function it_should_put_job_in_tube(SocketHandle $socketHandle): void
    {
        $payload = '12.3';

        $socketHandle->write("put 1 2 3 4\r\n")
            ->shouldBeCalled();
        $socketHandle->write($payload)
            ->shouldBeCalled();
        $socketHandle->write("\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(8, false)
            ->willReturn('INSERTED 32');

        $this->put(1, 2, 3, '12.3')
            ->shouldReturn(32);
    }

    public function it_should_throw_buried_exception_on_put(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(8, false)
            ->willReturn('BURIED 32');

        $this->shouldThrow(new JobBuriedException(32))
            ->duringPut(1, 2, 3, '12.3');
    }

    public function it_should_throw_expected_crlf_exception_on_put(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(8, false)
            ->willReturn('EXPECTED_CRLF');

        $this->shouldThrow(new ExpectedCRLFException())
            ->duringPut(1, 2, 3, '12.3');
    }

    public function it_should_throw_job_to_big_exception_on_put(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(8, false)
            ->willReturn('JOB_TOO_BIG');

        $this->shouldThrow(new JobToBigException())
            ->duringPut(1, 2, 3, '12.3');
    }

    public function it_should_throw_draining_exception_on_put(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(8, false)
            ->willReturn('DRAINING');

        $this->shouldThrow(new ServerInDrainingModeException())
            ->duringPut(1, 2, 3, '12.3');
    }

    public function it_should_use_tube(SocketHandle $socketHandle): void
    {
        $socketHandle->write("use foo\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(5 + 1 + 3, false)
            ->shouldBeCalled()
            ->willReturn('USING foo');

        $this->useTube('foo');
    }

    public function it_should_reserve_job(SocketHandle $socketHandle): void
    {
        $socketHandle->write("reserve\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(10, true)
            ->willReturn('RESERVED 32 4');

        $socketHandle->read(4)
            ->willReturn('12.4');
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $this->reserve()
            ->shouldBeLike(new Job(32, '12.4'));
    }

    public function it_should_throw_deadline_soon_exception_on_reserve(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(10, true)
            ->willReturn('DEADLINE_SOON');

        $this->shouldThrow(new DeadlineSoonException())
            ->duringReserve();
    }

    public function it_should_reserve_job_with_timeout(SocketHandle $socketHandle): void
    {
        $socketHandle->write("reserve-with-timeout 64\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(9, true)
            ->willReturn('RESERVED 32 4');

        $socketHandle->read(4)
            ->willReturn('12.4');
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $this->reserveWithTimeout(64)
            ->shouldBeLike(new Job(32, '12.4'));
    }

    public function it_should_throw_timed_out_exception_on_reserve_with_timeout(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(9, true)
            ->willReturn('TIMED_OUT');

        $this->shouldThrow(new ReserveTimedOutException(64))
            ->duringReserveWithTimeout(64);
    }

    public function it_should_throw_deadline_soon_exception_on_reserve_with_timeout(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(9, true)
            ->willReturn('DEADLINE_SOON');

        $this->shouldThrow(new DeadlineSoonException())
            ->duringReserveWithTimeout(64);
    }

    public function it_should_delete(SocketHandle $socketHandle): void
    {
        $socketHandle->write("delete 32\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(7, false)
            ->willReturn('DELETED');

        $this->delete(32);
    }

    public function it_should_throw_not_found_exception_on_delete(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(7, false)
            ->willReturn('NOT_FOUND');

        $this->shouldThrow(new JobNotFoundException(32))
            ->duringDelete(32);
    }

    public function it_should_release(SocketHandle $socketHandle): void
    {
        $socketHandle->write("release 32 1 2\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(6, false)
            ->willReturn('RELEASED');

        $this->release(32, 1, 2);
    }

    public function it_should_throw_buried_exception_on_release(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(6, false)
            ->willReturn('BURIED');

        $this->shouldThrow(new JobBuriedException(32))
            ->duringRelease(32, 1, 2);
    }

    public function it_should_throw_not_found_exception_on_release(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(6, false)
            ->willReturn('NOT_FOUND');

        $this->shouldThrow(new JobNotFoundException(32))
            ->duringRelease(32, 1, 2);
    }

    public function it_should_bury(SocketHandle $socketHandle): void
    {
        $socketHandle->write("bury 32 1\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(6, false)
            ->willReturn('BURIED');

        $this->bury(32, 1);
    }

    public function it_should_throw_not_found_exception_on_bury(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(6, false)
            ->willReturn('NOT_FOUND');

        $this->shouldThrow(new JobNotFoundException(32))
            ->duringBury(32, 1);
    }

    public function it_should_touch(SocketHandle $socketHandle): void
    {
        $socketHandle->write("touch 32\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(7, false)
            ->willReturn('TOUCHED');

        $this->touch(32);
    }

    public function it_should_throw_not_found_exception_on_touch(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(7, false)
            ->willReturn('NOT_FOUND');

        $this->shouldThrow(new JobNotFoundException(32))
            ->duringTouch(32);
    }

    public function it_should_watch(SocketHandle $socketHandle): void
    {
        $socketHandle->write("watch foo\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(8, false)
            ->willReturn('WATCHING 32');

        $this->watch('foo')
            ->shouldReturn(32);
    }

    public function it_should_ignore(SocketHandle $socketHandle): void
    {
        $socketHandle->write("ignore foo\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(10, false)
            ->willReturn('WATCHING 32');

        $this->ignore('foo')
            ->shouldReturn(32);
    }

    public function it_should_thwo_not_ignored_exception_on_ignore(SocketHandle $socketHandle): void
    {
        $socketHandle->write("ignore foo\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(10, false)
            ->willReturn('NOT_IGNORED');

        $this->shouldThrow(new NotIgnoredException('foo'))
            ->duringIgnore('foo');
    }

    public function it_should_peek(SocketHandle $socketHandle): void
    {
        $socketHandle->write("peek 32\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(9, false)
            ->willReturn('FOUND 32 4');

        $socketHandle->read(4)
            ->willReturn('12.3');
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $this->peek(32)
            ->shouldBeLike(new Job(32, '12.3'));
    }

    public function it_should_throw_not_found_on_peek(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(9, false)
            ->willReturn('NOT_FOUND');

        $this->shouldThrow(new JobNotFoundException(32))
            ->duringPeek(32);
    }

    public function it_should_peek_ready(SocketHandle $socketHandle): void
    {
        $socketHandle->write("peek-ready\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(9, false)
            ->willReturn('FOUND 32 4');

        $socketHandle->read(4)
            ->willReturn('12.3');
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $this->peekReady()
            ->shouldBeLike(new Job(32, '12.3'));
    }

    public function it_should_throw_not_found_on_peek_ready(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(9, false)
            ->willReturn('NOT_FOUND');

        $this->shouldThrow(new NotFoundException('No ready jobs'))
            ->duringPeekReady();
    }

    public function it_should_peek_delayed(SocketHandle $socketHandle): void
    {
        $socketHandle->write("peek-delayed\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(9, false)
            ->willReturn('FOUND 32 4');

        $socketHandle->read(4)
            ->willReturn('12.3');
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $this->peekDelayed()
            ->shouldBeLike(new Job(32, '12.3'));
    }

    public function it_should_throw_not_found_on_peek_delayed(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(9, false)
            ->willReturn('NOT_FOUND');

        $this->shouldThrow(new NotFoundException('No delayed jobs'))
            ->duringPeekDelayed();
    }

    public function it_should_peek_buried(SocketHandle $socketHandle): void
    {
        $socketHandle->write("peek-buried\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(9, false)
            ->willReturn('FOUND 32 4');

        $socketHandle->read(4)
            ->willReturn('12.3');
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $this->peekBuried()
            ->shouldBeLike(new Job(32, '12.3'));
    }

    public function it_should_throw_not_found_on_peek_buried(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(9, false)
            ->willReturn('NOT_FOUND');

        $this->shouldThrow(new NotFoundException('No buried jobs'))
            ->duringPeekBuried();
    }

    public function it_should_kick(SocketHandle $socketHandle): void
    {
        $socketHandle->write("kick 32\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(6, false)
            ->willReturn('KICKED 64');

        $this->kick(32)
            ->shouldReturn(64);
    }

    public function it_should_kick_job(SocketHandle $socketHandle): void
    {
        $socketHandle->write("kick-job 32\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(6, false)
            ->willReturn('KICKED');

        $this->kickJob(32);
    }

    public function it_should_throw_job_not_found_exception_on_kick_job(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(6, false)
            ->willReturn('NOT_FOUND');

        $this->shouldThrow(new JobNotFoundException(32))
            ->duringKickJob(32);
    }

    public function it_should_return_job_stats(SocketHandle $socketHandle, YamlParser $yamlParser): void
    {
        $socketHandle->write("stats-job 32\r\n")
            ->shouldBeCalled();

        $statsArray = ['foo'];

        $socketHandle->readLine(4, false)
            ->willReturn('OK 4');

        $yamlResponse = 'foo';

        $socketHandle->read(4)
            ->willReturn($yamlResponse);
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $yamlParser->parse($yamlResponse)
            ->willReturn($statsArray);

        $this->statsJob(32)
            ->shouldReturn($statsArray);
    }

    public function it_should_throw_job_not_found_exception_on_job_stats(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(4, false)
            ->willReturn('NOT_FOUND');

        $this->shouldThrow(new JobNotFoundException(32))
            ->duringStatsJob(32);
    }

    public function it_should_return_tube_stats(SocketHandle $socketHandle, YamlParser $yamlParser): void
    {
        $socketHandle->write("stats-tube bar\r\n")
            ->shouldBeCalled();

        $statsArray = ['foo'];

        $socketHandle->readLine(4, false)
            ->willReturn('OK 4');

        $yamlResponse = 'foo';

        $socketHandle->read(4)
            ->willReturn($yamlResponse);
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $yamlParser->parse($yamlResponse)
            ->willReturn($statsArray);

        $this->statsTube('bar')
            ->shouldReturn($statsArray);
    }

    public function it_should_throw_job_not_found_exception_on_tube_stats(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(4, false)
            ->willReturn('NOT_FOUND');

        $this->shouldThrow(new TubeNotFoundException('foo'))
            ->duringStatsTube('foo');
    }

    public function it_should_return_stats(SocketHandle $socketHandle, YamlParser $yamlParser): void
    {
        $socketHandle->write("stats\r\n")
            ->shouldBeCalled();

        $statsArray = ['foo'];

        $socketHandle->readLine(4, false)
            ->willReturn('OK 4');

        $yamlResponse = 'foo';

        $socketHandle->read(4)
            ->willReturn($yamlResponse);
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $yamlParser->parse($yamlResponse)
            ->willReturn($statsArray);

        $this->stats()
            ->shouldReturn($statsArray);
    }

    public function it_should_list_tubes(SocketHandle $socketHandle, YamlParser $yamlParser): void
    {
        $socketHandle->write("list-tubes\r\n")
            ->shouldBeCalled();

        $statsArray = ['foo'];

        $socketHandle->readLine(4, false)
            ->willReturn('OK 4');

        $yamlResponse = 'foo';

        $socketHandle->read(4)
            ->willReturn($yamlResponse);
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $yamlParser->parse($yamlResponse)
            ->willReturn($statsArray);

        $this->listTubes()
            ->toArray()
            ->shouldReturn($statsArray);
    }

    public function it_should_list_tube_used(SocketHandle $socketHandle): void
    {
        $socketHandle->write("list-tube-used\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(7, false)
            ->willReturn('USING foo');

        $this->listTubeUsed()
            ->shouldReturn('foo');
    }

    public function it_should_list_tubes_watching(SocketHandle $socketHandle, YamlParser $yamlParser): void
    {
        $socketHandle->write("list-tubes-watched\r\n")
            ->shouldBeCalled();

        $statsArray = ['foo'];

        $socketHandle->readLine(4, false)
            ->willReturn('OK 4');

        $yamlResponse = 'foo';

        $socketHandle->read(4)
            ->willReturn($yamlResponse);
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $yamlParser->parse($yamlResponse)
            ->willReturn($statsArray);

        $this->listTubesWatched()
            ->toArray()
            ->shouldReturn($statsArray);
    }

    public function it_should_pause_tube(SocketHandle $socketHandle): void
    {
        $socketHandle->write("pause-tube foo 32\r\n")
            ->shouldBeCalled();

        $socketHandle->readLine(6, false)
            ->willReturn('PAUSED');

        $this->pauseTube('foo', 32);
    }

    public function it_should_throw_tube_not_found_exception_on_pause_tube(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(6, false)
            ->willReturn('NOT_FOUND');

        $this->shouldThrow(new TubeNotFoundException('foo'))
            ->duringPauseTube('foo', 32);
    }

    public function it_should_wrap_socket_exception_on_write(SocketHandle $socketHandle): void
    {
        $e = new SocketException(SOCKET_ECONNREFUSED);

        $socketHandle->write(Argument::any())
            ->willThrow($e);

        $this->shouldThrow(
            new BeanstalkdLibException('Unexpected socket exception: Connection refused', $e)
        )
            ->duringListTubes();
    }

    public function it_should_wrap_socket_exception_on_read_line(SocketHandle $socketHandle): void
    {
        $e = new SocketException(SOCKET_ECONNREFUSED);

        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(Argument::any(), false)
            ->willThrow($e);

        $this->shouldThrow(
            new BeanstalkdLibException('Unexpected socket exception: Connection refused', $e)
        )
            ->duringListTubes();
    }

    public function it_should_wrap_socket_exception_on_read(SocketHandle $socketHandle): void
    {
        $e = new SocketException(SOCKET_ECONNREFUSED);

        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(Argument::any(), false)
            ->willReturn('OK 4');

        $socketHandle->read(4)
            ->willThrow($e);

        $this->shouldThrow(
            new BeanstalkdLibException('Unexpected socket exception: Connection refused', $e)
        )
            ->duringListTubes();
    }

    public function it_should_throw_exception_when_payload_response_is_not_valid(SocketHandle $socketHandle): void
    {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(Argument::any(), false)
            ->willReturn('OK 4');

        $socketHandle->read(4)
            ->willReturn('13.2');
        $socketHandle->read(2)
            ->willReturn('ab');

        $this->shouldThrow(
            new BeanstalkdLibException('Expected \r\n from server after payload')
        )
            ->duringListTubes();
    }

    public function it_should_throw_exception_when_yaml_is_not_valid(
        SocketHandle $socketHandle,
        YamlParser $yamlParser
    ): void {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(Argument::any(), false)
            ->willReturn('OK 4');

        $socketHandle->read(4)
            ->willReturn('13.2');
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $e = new YamlParseException('foo', 'bar');

        $yamlParser->parse('13.2')
            ->willThrow($e);

        $this->shouldThrow(
            new BeanstalkdLibException('Error while parsing YAML: "foo"', $e)
        )
            ->duringListTubes();
    }

    public function it_should_throw_exception_when_server_returns_invalid_leading_command(
        SocketHandle $socketHandle
    ): void {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(Argument::any(), false)
            ->willReturn('NOPE');

        $this->shouldThrow(
            new BeanstalkdLibException('Unexpected line from server: "NOPE"')
        )
            ->duringWatch('foo');
    }

    public function it_should_throw_exception_when_server_response_line_is_not_valid(
        SocketHandle $socketHandle
    ): void {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(Argument::any(), false)
            ->willReturn('WATCHING');

        $this->shouldThrow(
            new BeanstalkdLibException('Unexpected line from server: "WATCHING"')
        )
            ->duringWatch('foo');
    }

    public function it_should_throw_exception_when_number_not_provided_from_server_where_expected(
        SocketHandle $socketHandle
    ): void {
        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(Argument::any(), true)
            ->willReturn('RESERVED foo');

        $this->shouldThrow(
            new BeanstalkdLibException('Unexpected line from server: "RESERVED foo"')
        )
            ->duringReserve();
    }

    public function it_should_continue_on_interrupt_exception_when_graceful_exit_not_in_progress(
        SocketHandle $socketHandle,
        GracefulExit $gracefulExit
    ): void {
        $gracefulExit->inProgress()
            ->willReturn(false);

        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $interrupted = false;

        $socketHandle->readLine(Argument::any(), true)
            ->will(
                function () use (&$interrupted): string {
                    if ($interrupted) {
                        return 'RESERVED 32 4';
                    }

                    $interrupted = true;

                    throw new InterruptedCallSocketException();
                }
            );

        $socketHandle->read(4)
            ->willReturn('12.3');
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $this->reserve()
            ->shouldBeLike(new Job(32, '12.3'));
    }

    public function it_should_continue_on_try_again_exception_when_graceful_exit_not_in_progress(
        SocketHandle $socketHandle,
        GracefulExit $gracefulExit
    ): void {
        $gracefulExit->inProgress()
            ->willReturn(false);

        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $interrupted = false;

        $socketHandle->readLine(Argument::any(), true)
            ->will(
                function () use (&$interrupted): string {
                    if ($interrupted) {
                        return 'RESERVED 32 4';
                    }

                    $interrupted = true;

                    throw new TryAgainSocketException();
                }
            );

        $socketHandle->read(4)
            ->willReturn('12.3');
        $socketHandle->read(2)
            ->willReturn("\r\n");

        $this->reserve()
            ->shouldBeLike(new Job(32, '12.3'));
    }

    public function it_should_not_continue_on_interrupt_exception_when_graceful_exit_in_progress(
        SocketHandle $socketHandle,
        GracefulExit $gracefulExit
    ): void {
        $gracefulExit->inProgress()
            ->willReturn(true);

        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(Argument::any(), true)
            ->willThrow(new InterruptedCallSocketException());

        $this->shouldThrow(new ReserveInterruptedException())
            ->duringReserve();
    }

    public function it_should_not_continue_on_try_again_exception_when_graceful_exit_in_progress(
        SocketHandle $socketHandle,
        GracefulExit $gracefulExit
    ): void {
        $gracefulExit->inProgress()
            ->willReturn(true);

        $socketHandle->write(Argument::any())
            ->shouldBeCalled();

        $socketHandle->readLine(Argument::any(), true)
            ->willThrow(new TryAgainSocketException());

        $this->shouldThrow(new ReserveInterruptedException())
            ->duringReserve();
    }
}
