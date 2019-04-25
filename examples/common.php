<?php

declare(strict_types=1);

use Zlikavac32\BeanstalkdLib\Adapter\PHP\Socket\NativePHPSocket;
use Zlikavac32\BeanstalkdLib\Adapter\Symfony\Yaml\SymfonyYamlParser;
use Zlikavac32\BeanstalkdLib\InterruptHandler\GracefulExitInterruptHandler;
use Zlikavac32\BeanstalkdLib\Protocol\ProtocolOverSocket;
use Zlikavac32\BeanstalkdLib\Protocol\StateAwareProtocol;
use Zlikavac32\BeanstalkdLib\Socket\LazySocket;

require_once __DIR__.'/../vendor/autoload.php';

// We create lazy native socket
$socket = new LazySocket(
    new NativePHPSocket(60000000)
);

// Yaml parser is used for Beanstalkd protocol parsing
$yamlParser = new SymfonyYamlParser();

// Graceful exit is shared to inspect whether graceful exit is in progress or not
$gracefulExit = new GracefulExitInterruptHandler();

// Protocol over socket that is aware of currently used tube
// So that multiple calls to useTube do not make additional
// requests to the server
$protocol = new StateAwareProtocol(
    new ProtocolOverSocket(
        $socket->open('127.0.0.1', 11300),
        $gracefulExit,
        $yamlParser
    )
);
