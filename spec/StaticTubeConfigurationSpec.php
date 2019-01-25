<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib;

use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\Serializer;
use Zlikavac32\BeanstalkdLib\StaticTubeConfiguration;

class StaticTubeConfigurationSpec extends ObjectBehavior
{
    public function let(Serializer $serializer): void {
        $this->beConstructedWith(1, 2, 3, 4, $serializer);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(StaticTubeConfiguration::class);
    }

    public function it_should_have_default_delay(): void {
        $this->defaultDelay()->shouldReturn(1);
    }

    public function it_should_have_default_priorty(): void {
        $this->defaultPriority()->shouldReturn(2);
    }

    public function it_should_have_default_time_to_run(): void {
        $this->defaultTimeToRun()->shouldReturn(3);
    }

    public function it_should_have_default_pause_delay(): void {
        $this->defaultTubePauseDelay()->shouldReturn(4);
    }

    public function it_should_have_serializer(Serializer $serializer): void {
        $this->serializer()->shouldReturn($serializer);
    }
}
