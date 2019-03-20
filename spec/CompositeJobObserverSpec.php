<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib;

use Exception;
use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\CompositeJobObserver;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\JobObserver;

class CompositeJobObserverSpec extends ObjectBehavior {

    public function let(JobObserver $firstObserver, JobObserver $secondObserver): void {
        $this->beConstructedWith($firstObserver, $secondObserver);
    }

    public function it_is_initializable(): void {
        $this->shouldHaveType(CompositeJobObserver::class);
    }

    public function it_should_propagate_starting_event(JobObserver $firstObserver, JobObserver $secondObserver, JobHandle $jobHandle): void {
        $firstObserver->starting($jobHandle)->shouldBeCalled();
        $secondObserver->starting($jobHandle)->shouldBeCalled();

        $this->starting($jobHandle);
    }

    public function it_should_propagate_finished_event(JobObserver $firstObserver, JobObserver $secondObserver, JobHandle $jobHandle): void {
        $firstObserver->finished($jobHandle, 4.9)->shouldBeCalled();
        $secondObserver->finished($jobHandle, 4.9)->shouldBeCalled();

        $this->finished($jobHandle, 4.9);
    }

    public function it_should_propagate_failed_event(JobObserver $firstObserver, JobObserver $secondObserver, JobHandle $jobHandle): void {
        $e = new Exception();

        $firstObserver->failed($jobHandle, $e, 5.2)->shouldBeCalled();
        $secondObserver->failed($jobHandle, $e, 5.2)->shouldBeCalled();

        $this->failed($jobHandle, $e, 5.2);
    }
}
