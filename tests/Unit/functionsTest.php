<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Tests\Unit;

use PHPUnit\Framework\TestCase;
use function Zlikavac32\BeanstalkdLib\microTimeToHuman;

class functionsTest extends TestCase {

    /**
     * @test
     */
    public function micro_time_to_human_formats_just_hours_when_minutes_iz_zero(): void {
        self::assertSame('10 h', microTimeToHuman(10 * 3600 + 1));
    }

    /**
     * @test
     */
    public function micro_time_to_human_formats_hours_and_minutes(): void {
        self::assertSame('33 h 44 min', microTimeToHuman(33 * 3600 + 44 * 60 + 1));
    }

    /**
     * @test
     */
    public function micro_time_to_human_formats_just_minutes_when_seconds_iz_zero(): void {
        self::assertSame('10 min', microTimeToHuman(10 * 60 + .1));
    }

    /**
     * @test
     */
    public function micro_time_to_human_formats_minutes_and_seconds(): void {
        self::assertSame('10 min 4 s', microTimeToHuman(10 * 60 + 4 + .1));
    }

    /**
     * @test
     */
    public function micro_time_to_human_formats_just_seconds_when_miliseconds_is_zero(): void {
        self::assertSame('25 s', microTimeToHuman(25 + 1e-4));
    }

    /**
     * @test
     */
    public function micro_time_to_human_formats_seconds_and_miliseconds(): void {
        self::assertSame('25 s 124 ms', microTimeToHuman(25 + 124e-3 + 1e-4));
    }

    /**
     * @test
     */
    public function micro_time_to_human_formats_just_miliseconds_when_microseconds_is_zero(): void {
        self::assertSame('45 ms', microTimeToHuman(45e-3 + 1e-7));
    }

    /**
     * @test
     */
    public function micro_time_to_human_formats_miliseconds_and_microseconds(): void {
        self::assertSame('5 ms 14 us', microTimeToHuman(5e-3 + 14e-6 + 1e-7));
    }

    /**
     * @test
     */
    public function micro_time_to_human_formats_just_microseconds(): void {
        self::assertSame('42 us', microTimeToHuman(42e-6 + 1e-7));
    }
}
