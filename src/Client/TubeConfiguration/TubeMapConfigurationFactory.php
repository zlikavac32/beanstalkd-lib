<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Client\TubeConfiguration;

use Ds\Map;
use LogicException;

class TubeMapConfigurationFactory implements TubeConfigurationFactory
{

    /**
     * @var Map|TubeConfiguration[]
     */
    private $tubeConfigurations;

    public function __construct(Map $tubeConfigurations)
    {
        $this->tubeConfigurations = $tubeConfigurations;
    }

    public function createForTube(string $tubeName): TubeConfiguration
    {
        if (!$this->tubeConfigurations->hasKey($tubeName)) {
            throw new LogicException(sprintf('Configuration for tube "%s" does not exist.', $tubeName));
        }

        return $this->tubeConfigurations->get($tubeName);
    }
}
