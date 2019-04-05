<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Adapter\Symfony\Console;

use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\Adapter\Symfony\Console\ClearGracefulExitEventListener;
use Zlikavac32\BeanstalkdLib\ClearableGracefulExit;
use Zlikavac32\BeanstalkdLib\GracefulExit;

class ClearGracefulExitEventListenerSpec extends ObjectBehavior
{

    public function let(GracefulExit $gracefulExit): void
    {
        $this->beConstructedWith($gracefulExit);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ClearGracefulExitEventListener::class);
    }

    public function it_should_do_nothing_if_graceful_exit_is_not_clearable(): void
    {
        $this->onConsoleTerminate();
    }

    public function it_should_clear_on_clearable_graceful_exit(ClearableGracefulExit $clearableGracefulExit): void
    {
        $this->beConstructedWith($clearableGracefulExit);

        $clearableGracefulExit->clear()
            ->shouldBeCalled();

        $this->onConsoleTerminate();
    }
}
