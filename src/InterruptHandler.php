<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

interface InterruptHandler
{

    public function handle(): void;
}
