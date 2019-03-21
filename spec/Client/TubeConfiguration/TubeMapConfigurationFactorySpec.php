<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Client\TubeConfiguration;

use Ds\Map;
use LogicException;
use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeConfiguration;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeMapConfigurationFactory;

class TubeMapConfigurationFactorySpec extends ObjectBehavior
{
    public function let(): void {
        $this->beConstructedWith(new Map());
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(TubeMapConfigurationFactory::class);
    }

    public function it_should_throw_exception_if_tube_not_found(): void {
        $this->shouldThrow(new LogicException('Configuration for tube "foo" does not exist.'))
            ->duringCreateForTube('foo');
    }

    public function it_should_return_configuration_when_tube_exists(TubeConfiguration $tubeConfiguration): void {
       $this->beConstructedWith(new Map([
           'foo' => $tubeConfiguration->getWrappedObject()
       ]));

        $this->createForTube('foo')->shouldReturn($tubeConfiguration);
    }
}
