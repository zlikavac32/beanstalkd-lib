<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Protocol;

use Ds\Sequence;
use Ds\Set;
use Ds\Vector;
use LogicException;
use Zlikavac32\BeanstalkdLib\BeanstalkdLibException;
use Zlikavac32\BeanstalkdLib\DeadlineSoonException;
use Zlikavac32\BeanstalkdLib\ExpectedCRLFException;
use Zlikavac32\BeanstalkdLib\GracefulExit;
use Zlikavac32\BeanstalkdLib\InterruptedCallSocketException;
use Zlikavac32\BeanstalkdLib\Job;
use Zlikavac32\BeanstalkdLib\JobBuriedException;
use Zlikavac32\BeanstalkdLib\JobNotFoundException;
use Zlikavac32\BeanstalkdLib\JobToBigException;
use Zlikavac32\BeanstalkdLib\NotFoundException;
use Zlikavac32\BeanstalkdLib\NotIgnoredException;
use Zlikavac32\BeanstalkdLib\Protocol;
use Zlikavac32\BeanstalkdLib\ReserveInterruptedException;
use Zlikavac32\BeanstalkdLib\ReserveTimedOutException;
use Zlikavac32\BeanstalkdLib\ServerInDrainingModeException;
use Zlikavac32\BeanstalkdLib\SocketException;
use Zlikavac32\BeanstalkdLib\SocketHandle;
use Zlikavac32\BeanstalkdLib\TryAgainSocketException;
use Zlikavac32\BeanstalkdLib\TubeNotFoundException;
use Zlikavac32\BeanstalkdLib\YamlParseException;
use Zlikavac32\BeanstalkdLib\YamlParser;

class ProtocolOverSocket implements Protocol
{

    private const T_INSERTED = "INSERTED";
    private const T_BURIED = "BURIED";
    private const T_EXPECTED_CRLF = "EXPECTED_CRLF";
    private const T_JOB_TOO_BIG = "JOB_TOO_BIG";
    private const T_DRAINING = "DRAINING";
    private const T_DEADLINE_SOON = "DEADLINE_SOON";
    private const T_RESERVED = "RESERVED";
    private const T_USING = "USING";
    private const T_TIMED_OUT = "TIMED_OUT";
    private const T_DELETED = "DELETED";
    private const T_NOT_FOUND = "NOT_FOUND";
    private const T_RELEASED = "RELEASED";
    private const T_TOUCHED = "TOUCHED";
    private const T_WATCHING = "WATCHING";
    private const T_NOT_IGNORED = "NOT_IGNORED";
    private const T_FOUND = "FOUND";
    private const T_KICKED = "KICKED";
    private const T_OK = "OK";
    private const T_PAUSED = "PAUSED";

    /**
     * @var SocketHandle
     */
    private $socketHandle;
    /**
     * @var YamlParser
     */
    private $yamlParser;
    /**
     * @var GracefulExit
     */
    private $gracefulExit;

    public function __construct(SocketHandle $socketHandle, GracefulExit $gracefulExit, YamlParser $yamlParser)
    {
        $this->socketHandle = $socketHandle;
        $this->gracefulExit = $gracefulExit;
        $this->yamlParser = $yamlParser;
    }

    /**
     * @inheritdoc
     */
    public function put(int $priority, int $delay, int $timeToRun, string $payload): int
    {
        $this->writeToSocket(\sprintf("put %d %d %d %d\r\n", $priority, $delay, $timeToRun, \strlen($payload)));
        $this->writeToSocket($payload);
        $this->writeToSocket("\r\n");

        return $this->parseLineFromSocket(
            8, // min_len(INSERTED \d|BURIED \d|EXPECTED_CRLF|JOB_TO_BIG|DRAINING)
            [
                self::T_INSERTED      => [
                    ['int'],
                    function (int $id): int {
                        return $id;
                    },
                ],
                self::T_BURIED        => [
                    ['int'],
                    function (int $id): void {
                        throw new JobBuriedException($id);
                    },
                ],
                self::T_EXPECTED_CRLF => [
                    [],
                    function (): void {
                        throw new ExpectedCRLFException();
                    },
                ],
                self::T_JOB_TOO_BIG   => [
                    [],
                    function (): void {
                        throw new JobToBigException();
                    },
                ],
                self::T_DRAINING      => [
                    [],
                    function (): void {
                        throw new ServerInDrainingModeException();
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function useTube(string $tube): void
    {
        $this->writeToSocket(\sprintf("use %s\r\n", $tube));

        $this->parseLineFromSocket(
            \strlen(self::T_USING) + 1 + \strlen($tube),
            [
                self::T_USING => [
                    ['ignore'],
                    function (): void {
                        return;
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function reserve(): Job
    {
        $this->writeToSocket("reserve\r\n");

        return $this->parseLineFromSocket(
            10, // min_len(RESERVED \d|DEADLINE_SOON)
            [
                self::T_RESERVED      => [
                    ['int', 'int'],
                    function (int $id, int $payloadSize): Job {
                        return new Job($id, $this->readPayload($payloadSize));
                    },
                ],
                self::T_DEADLINE_SOON => [
                    [],
                    function (): void {
                        throw new DeadlineSoonException();
                    },
                ],
            ],
            true
        );
    }

    /**
     * @inheritdoc
     */
    public function reserveWithTimeout(int $timeout): Job
    {
        $this->writeToSocket(\sprintf("reserve-with-timeout %d\r\n", $timeout));

        return $this->parseLineFromSocket(
            9, // min_len(RESERVED \d|TIMED_OUT|DEADLINE_SOON)
            [
                self::T_RESERVED      => [
                    ['int', 'int'],
                    function (int $id, int $payloadSize): Job {
                        return new Job($id, $this->readPayload($payloadSize));
                    },
                ],
                self::T_TIMED_OUT     => [
                    [],
                    function () use ($timeout): void {
                        throw new ReserveTimedOutException($timeout);
                    },
                ],
                self::T_DEADLINE_SOON => [
                    [],
                    function (): void {
                        throw new DeadlineSoonException();
                    },
                ],
            ],
            true
        );
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id): void
    {
        $this->writeToSocket(\sprintf("delete %d\r\n", $id));

        $this->parseLineFromSocket(
            7, // min_len(DELETED|NOT_FOUND)
            [
                self::T_DELETED   => [
                    [],
                    function (): void {

                    },
                ],
                self::T_NOT_FOUND => [
                    [],
                    function () use ($id): void {
                        throw new JobNotFoundException($id);
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function release(int $id, int $priority, int $delay): void
    {
        $this->writeToSocket(\sprintf("release %d %d %d\r\n", $id, $priority, $delay));

        $this->parseLineFromSocket(
            6, // min_len(RELEASED|BURIED|NOT_FOUND)
            [
                self::T_RELEASED  => [
                    [],
                    function (): void {

                    },
                ],
                self::T_BURIED    => [
                    [],
                    function () use ($id): void {
                        throw new JobBuriedException($id);
                    },
                ],
                self::T_NOT_FOUND => [
                    [],
                    function () use ($id): void {
                        throw new JobNotFoundException($id);
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function bury(int $id, int $priority): void
    {
        $this->writeToSocket(\sprintf("bury %d %d\r\n", $id, $priority));

        $this->parseLineFromSocket(
            6, // min_len(BURIED|NOT_FOUND)
            [
                self::T_BURIED    => [
                    [],
                    function (): void {

                    },
                ],
                self::T_NOT_FOUND => [
                    [],
                    function () use ($id): void {
                        throw new JobNotFoundException($id);
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function touch(int $id): void
    {
        $this->writeToSocket(\sprintf("touch %d\r\n", $id));

        $this->parseLineFromSocket(
            7, // min_len(TOUCHED|NOT_FOUND)
            [
                self::T_TOUCHED   => [
                    [],
                    function (): void {

                    },
                ],
                self::T_NOT_FOUND => [
                    [],
                    function () use ($id): void {
                        throw new JobNotFoundException($id);
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function watch(string $tube): int
    {
        $this->writeToSocket(\sprintf("watch %s\r\n", $tube));

        return $this->parseLineFromSocket(
            8, // len(WATCHING)
            [
                self::T_WATCHING => [
                    ['int'],
                    function (int $countOfWatchedTubes): int {
                        return $countOfWatchedTubes;
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function ignore(string $tube): int
    {
        $this->writeToSocket(\sprintf("ignore %s\r\n", $tube));

        return $this->parseLineFromSocket(
            10, // min_len(WATCHING \w|NOT_IGNORED)
            [
                self::T_WATCHING    => [
                    ['int'],
                    function (int $countOfWatchedTubes): int {
                        return $countOfWatchedTubes;
                    },
                ],
                self::T_NOT_IGNORED => [
                    [],
                    function () use ($tube): void {
                        throw new NotIgnoredException($tube);
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function peek(int $id): Job
    {
        return $this->peekInternal(
            \sprintf("peek %d\r\n", $id),
            function () use ($id): void {
                throw new JobNotFoundException($id);
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function peekReady(): Job
    {
        return $this->peekInternal(
            "peek-ready\r\n",
            function (): void {
                throw new NotFoundException('No ready jobs');
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function peekDelayed(): Job
    {
        return $this->peekInternal(
            "peek-delayed\r\n",
            function (): void {
                throw new NotFoundException('No delayed jobs');
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function peekBuried(): Job
    {
        return $this->peekInternal(
            "peek-buried\r\n",
            function (): void {
                throw new NotFoundException('No buried jobs');
            }
        );
    }

    private function peekInternal(string $command, callable $notFoundCallback): Job
    {
        $this->writeToSocket($command);

        return $this->parseLineFromSocket(
            9, // min_len(FOUND \d \d|NOT_FOUND)
            [
                self::T_FOUND     => [
                    ['int', 'int'],
                    function (int $id, int $payloadSize): Job {
                        return new Job($id, $this->readPayload($payloadSize));
                    },
                ],
                self::T_NOT_FOUND => [
                    [],
                    $notFoundCallback,
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function kick(int $numberOfJobs): int
    {
        $this->writeToSocket(\sprintf("kick %d\r\n", $numberOfJobs));

        return $this->parseLineFromSocket(
            6, // len(KICKED)
            [
                self::T_KICKED => [
                    ['int'],
                    function (int $numberOfKickedJobs): int {
                        return $numberOfKickedJobs;
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function kickJob(int $id): void
    {
        $this->writeToSocket(\sprintf("kick-job %d\r\n", $id));

        $this->parseLineFromSocket(
            6, // min_len(KICKED|NOT_FOUND)
            [
                self::T_KICKED    => [
                    [],
                    function (): void {

                    },
                ],
                self::T_NOT_FOUND => [
                    [],
                    function () use ($id): void {
                        throw new JobNotFoundException($id);
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function statsJob(int $id): array
    {
        $this->writeToSocket(\sprintf("stats-job %d\r\n", $id));

        return $this->parseLineFromSocket(
            4, // min_len(OK \d|NOT_FOUND)
            [
                self::T_OK        => [
                    ['int'],
                    function (int $payloadSize): array {
                        return $this->readYamlPayload($payloadSize);
                    },
                ],
                self::T_NOT_FOUND => [
                    [],
                    function () use ($id): void {
                        throw new JobNotFoundException($id);
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function statsTube(string $tube): array
    {
        $this->writeToSocket(\sprintf("stats-tube %s\r\n", $tube));

        return $this->parseLineFromSocket(
            4, // min_len(OK \d|NOT_FOUND)
            [
                self::T_OK        => [
                    ['int'],
                    function (int $payloadSize): array {
                        return $this->readYamlPayload($payloadSize);
                    },
                ],
                self::T_NOT_FOUND => [
                    [],
                    function () use ($tube): void {
                        throw new TubeNotFoundException($tube);
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function stats(): array
    {
        $this->writeToSocket("stats\r\n");

        return $this->parseLineFromSocket(
            4, // len(OK \d)
            [
                self::T_OK => [
                    ['int'],
                    function (int $payloadSize): array {
                        $stats = $this->readYamlPayload($payloadSize);

                        $stats['hostname'] = (string) $stats['hostname'];

                        return $stats;
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function listTubes(): Sequence
    {
        $this->writeToSocket("list-tubes\r\n");

        return new Vector(
            $this->parseLineFromSocket(
                4, // len(OK \d)
                [
                    self::T_OK => [
                        ['int'],
                        function (int $payloadSize): array {
                            return $this->readYamlPayload($payloadSize);
                        },
                    ],
                ]
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function listTubeUsed(): string
    {
        $this->writeToSocket("list-tube-used\r\n");

        return $this->parseLineFromSocket(
            7, // len(USING \w)
            [
                self::T_USING => [
                    ['pass'],
                    function (string $tube): string {
                        return $tube;
                    },
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function listTubesWatched(): Set
    {
        $this->writeToSocket("list-tubes-watched\r\n");

        return new Set(
            $this->parseLineFromSocket(
                4, // len(OK \d)
                [
                    self::T_OK => [
                        ['int'],
                        function (int $payloadSize): array {
                            if ($payloadSize === 0) {
                                return [];
                            }
                            return $this->readYamlPayload($payloadSize);
                        },
                    ],
                ]
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function pauseTube(string $tube, int $delay): void
    {
        $this->writeToSocket(\sprintf("pause-tube %s %d\r\n", $tube, $delay));

        $this->parseLineFromSocket(
            6, // min_len(PAUSED|NOT_FOUND)
            [
                self::T_PAUSED    => [
                    [],
                    function (): void {

                    },
                ],
                self::T_NOT_FOUND => [
                    [],
                    function () use ($tube): void {
                        throw new TubeNotFoundException($tube);
                    },
                ],
            ]
        );
    }

    private function writeToSocket(string $buffer): void
    {
        try {
            $this->socketHandle->write($buffer);
        } catch (SocketException $e) {
            throw $this->wrapSocketException($e);
        }
    }

    private function readLineFromSocket(int $minimumLength, bool $interruptible): string
    {
        while (true) {
            try {
                return $this->socketHandle->readLine($minimumLength, $interruptible);
            } catch (InterruptedCallSocketException | TryAgainSocketException $e) {
                if ($this->gracefulExit->inProgress()) {
                    throw new ReserveInterruptedException($e);
                }

                continue;
            } catch (SocketException $e) {
                throw $this->wrapSocketException($e);
            }
        }
    }

    private function readFromSocket(int $length): string
    {
        try {
            return $this->socketHandle->read($length);
        } catch (SocketException $e) {
            throw $this->wrapSocketException($e);
        }
    }

    private function wrapSocketException(SocketException $e): BeanstalkdLibException
    {
        return new BeanstalkdLibException(\sprintf('Unexpected socket exception: %s', $e->getMessage()), $e);
    }

    private function parseLineFromSocket(int $minimumLineLength, array $matchers, bool $interruptible = false)
    {
        $line = $this->readLineFromSocket($minimumLineLength, $interruptible);

        $lineParts = \explode(' ', $line);

        $firstToken = $lineParts[0];
        $count = \count($lineParts);

        if (!isset($matchers[$firstToken])) {
            $this->throwUnexpectedLineException($line);
        }

        [$expectedTokens, $callback] = $matchers[$firstToken];

        if (\count($expectedTokens) !== $count - 1) {
            $this->throwUnexpectedLineException($line);
        }

        $normalizedArgs = [];

        for ($i = 1; $i < $count; $i++) {
            switch ($expectedTokens[$i - 1]) {
                case 'int':
                    if (!\preg_match('/^\d+$/', $lineParts[$i])) {
                        $this->throwUnexpectedLineException($line);
                    }

                    $normalizedArgs[] = (int)$lineParts[$i];

                    break;
                case 'pass':
                    $normalizedArgs[] = $lineParts[$i];

                    break;
                case 'ignore':
                    break;
                default:
                    throw new LogicException(\sprintf('Unknown expected token %s', $expectedTokens[$i - 1]));
            }
        }

        return $callback(...$normalizedArgs);
    }

    private function readPayload(int $size): string
    {
        if ($size === 0) {
            return '';
        }

        $payload = $this->readFromSocket($size);

        if ("\r\n" !== $this->readFromSocket(2)) {
            throw new BeanstalkdLibException('Expected \r\n from server after payload');
        }

        return $payload;
    }

    private function readYamlPayload(int $size): array
    {
        $payload = $this->readPayload($size);

        try {
            return $this->yamlParser->parse($payload);
        } catch (YamlParseException $e) {
            throw new BeanstalkdLibException(\sprintf('Error while parsing YAML: "%s"', $e->getMessage()), $e);
        }
    }

    private function throwUnexpectedLineException(string $line): void
    {
        throw new BeanstalkdLibException(\sprintf('Unexpected line from server: "%s"', $line));
    }
}
