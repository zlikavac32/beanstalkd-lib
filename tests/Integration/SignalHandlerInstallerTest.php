<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Tests\Integration;

use Ds\Map;
use PHPUnit\Framework\TestCase;
use Zlikavac32\BeanstalkdLib\InterruptHandler;
use Zlikavac32\BeanstalkdLib\SignalHandlerInstaller;

class SignalHandlerInstallerTest extends TestCase
{

    /**
     * @var bool
     */
    private static $originalUseAsyncSignals;

    private static array $signalNames = [SIGINT => 'SIGINT', SIGQUIT => 'SIGQUIT', SIGTERM => 'SIGTERM'];

    private ?MockInterruptHandler $interruptHandler;

    private ?SignalHandlerInstaller $signalHandlerInstaller;

    private ?Map $originalHandlers;

    private array $signals = [SIGINT, SIGTERM, SIGQUIT];

    public static function setUpBeforeClass(): void
    {
        self::$originalUseAsyncSignals = pcntl_async_signals(true);
    }

    public static function tearDownAfterClass(): void
    {
        pcntl_async_signals(self::$originalUseAsyncSignals);
    }

    public function setUp(): void
    {
        $this->interruptHandler = new MockInterruptHandler();
        $this->signalHandlerInstaller = new SignalHandlerInstaller($this->interruptHandler);

        $this->swapCurrentHandlersForStubs();
    }

    private function swapCurrentHandlersForStubs(): void
    {
        $this->originalHandlers = new Map();

        foreach ($this->signals as $signal) {
            $this->originalHandlers->put($signal, pcntl_signal_get_handler($signal));

            pcntl_signal($signal, SIG_IGN);
        }
    }

    public function tearDown(): void
    {
        $this->restoreOriginalHandlers();

        $this->interruptHandler = null;
        $this->signalHandlerInstaller = null;
    }

    private function restoreOriginalHandlers(): void
    {
        foreach ($this->originalHandlers as $signal => $handler) {
            pcntl_signal($signal, $handler);
        }
    }

    public function async_signals_should_be_turned_on(): void
    {
        pcntl_async_signals(false);

        $this->signalHandlerInstaller->install();

        self::assertTrue(pcntl_async_signals(), 'pcntl_async_signals not set to true');
    }

    /**
     * @test
     */
    public function sigint_should_be_caught(): void
    {
        $this->signalHandlerInstaller->install();

        $this->killMeWith(SIGINT);

        self::assertTrue($this->interruptHandler->wasCalled());
    }

    /**
     * @test
     */
    public function sigterm_should_be_caught(): void
    {
        $this->signalHandlerInstaller->install();

        $this->killMeWith(SIGTERM);

        self::assertTrue($this->interruptHandler->wasCalled());
    }

    /**
     * @test
     */
    public function sigquit_should_be_caught(): void
    {
        $this->signalHandlerInstaller->install();

        $this->killMeWith(SIGQUIT);

        self::assertTrue($this->interruptHandler->wasCalled());
    }

    /**
     * @test
     */
    public function old_handlers_should_be_restored(): void
    {
        $expectedHandlers = new Map();

        foreach ($this->signals as $signal) {
            $handler = function (): void {
            };

            $expectedHandlers->put($signal, $handler);
            pcntl_signal($signal, $handler);
        }

        $this->signalHandlerInstaller->install();

        $this->signalHandlerInstaller->uninstall();

        foreach ($this->signals as $signal) {
            self::assertSame(
                $expectedHandlers->get($signal),
                pcntl_signal_get_handler($signal),
                sprintf('Signal handlers for %s do not match', self::$signalNames[$signal])
            );
        }
    }

    public function old_async_signals_value_should_be_restored(): void
    {
        pcntl_async_signals(false);

        $this->signalHandlerInstaller->install();

        $this->signalHandlerInstaller->uninstall();

        self::assertFalse(pcntl_async_signals(), 'pcntl_async_signals not restored to false');
    }

    private function killMeWith(int $signal): void
    {
        posix_kill(posix_getpid(), $signal);
    }
}

/**
 * @internal
 */
class MockInterruptHandler implements InterruptHandler
{

    private $called = false;

    public function handle(): void
    {
        $this->called = true;
    }

    public function wasCalled(): bool
    {
        return $this->called;
    }
}
