<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

class DoNothingInterruptHandler implements InterruptHandler {

    public function handle(): void {

    }
}
