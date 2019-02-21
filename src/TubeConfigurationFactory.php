<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

interface TubeConfigurationFactory {

    public function createForTube(string $tubeName): TubeConfiguration;
}
