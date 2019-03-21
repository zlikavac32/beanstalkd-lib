<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\InterruptHandler;

use Zlikavac32\BeanstalkdLib\InterruptHandler;

class DoNothingInterruptHandler implements InterruptHandler {

    public function handle(): void {

    }
}
