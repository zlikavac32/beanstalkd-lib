<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Runner;

use Exception;
use PhpSpec\ObjectBehavior;
use Zlikavac32\BeanstalkdLib\JobHandle;
use Zlikavac32\BeanstalkdLib\Runner\CompositeJobObserver;
use Zlikavac32\BeanstalkdLib\Runner\JobObserver;

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

    public function it_should_have_injected_observers(JobObserver $firstObserver, JobObserver $secondObserver): void {
        $this->has($firstObserver)->shouldReturn(true);
        $this->has($secondObserver)->shouldReturn(true);
    }

    public function it_should_append_observer_on_empty_composite(JobHandle $jobHandle, JobObserver $observer): void {
        $this->beConstructedWith();

        $this->append($observer);

        $observer->starting($jobHandle)->shouldBeCalled();

        $this->starting($jobHandle);
    }

    public function it_should_have_appended_observer(JobHandle $jobHandle, JobObserver $observer): void {
        $this->has($observer)->shouldReturn(false);

        $this->append($observer);

        $this->has($observer)->shouldReturn(true);

        $observer->starting($jobHandle)->shouldBeCalled();

        $this->starting($jobHandle);
    }

    public function it_should_remove_observer(JobObserver $observer): void {
        $this->append($observer);

        $this->has($observer)->shouldReturn(true);

        $this->remove($observer);

        $this->has($observer)->shouldReturn(false);
    }

    public function it_should_allow_to_add_again_same_observer(JobObserver $observer): void {
        $this->append($observer);

        $this->has($observer)->shouldReturn(true);

        $this->remove($observer);

        $this->has($observer)->shouldReturn(false);

        $this->append($observer);

        $this->has($observer)->shouldReturn(true);
    }
}
