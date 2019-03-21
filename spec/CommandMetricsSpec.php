<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib;

use Ds\Map;
use LogicException;
use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\Command;
use Zlikavac32\BeanstalkdLib\CommandMetrics;

class CommandMetricsSpec extends ObjectBehavior
{

    public function let(): void
    {
        $map = new Map();

        foreach (Command::values() as $item) {
            $map->put($item, $item->ordinal());
        }

        $this->beConstructedWith($map);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CommandMetrics::class);
    }

    public function it_should_throw_exception_if_map_has_missing_elements(): void
    {
        $this->beConstructedWith(new Map());

        $this->shouldThrow(new LogicException('Stats for command "PUT" are missing'))
            ->duringInstantiation();
    }

    public function it_should_proper_values(): void
    {
        foreach (Command::values() as $command) {
            $this->numberOf($command)
                ->shouldReturn($command->ordinal());
        }
    }
}
