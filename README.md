# Beanstalkd lib

A different Beanstalkd client library.

## Table of contents

1. [Introduction](#introduction)
    1. [Protocol](#protocol)
    1. [Client](#client)
    1. [Framework agnostic](#framework-agnostic)
    1. [Various signal handling strategies](#carious-signal-handling-strategies)
    1. [Graceful exit support](#graceful-exit-support)
    1. [Oriented towards decorators](#oriented-towards-decorators)
    1. [Auto-touch job](#auto-touch-job)
    1. [Tube specific configuration](#tube-specific-configuration)
1. [Installation](#installation)
1. [Usage](#usage)
    1. [Configuration](#configuration)
    1. [Producers](#producers)
    1. [Workers](#workers)
1. [Examples](#examples)

## Introduction

Library is composed of two layers, `Protocol` and `Client`. `Protocol` is one-to-one mapping of Beanstalkd commands, and `Client` is higher level API which decomposes functionality into multiple interfaces.

Job dispatcher/runner functionality is also provided, as well as support for signal handling.

### Protocol

Interface for the protocol is `Zlikavac32\BeanstalkdLib\Protocol` and default implementation is provided as `Zlikavac32\BeanstalkdLib\Protocol\ProtocolOverSocket`.

Methods represent Beanstalkd commands as they are defined in the protocol. That means that there are no default values in the methods. Every distinct Beanstalkd error has it's own exception to make error recovery easier.

### Client

Client is higher level API for Beanstalkd, that consists of three main interfaces:

- `Zlikavac32\BeanstalkdLib\Client` - interface towards generic server commands
- `Zlikavac32\BeanstalkdLib\TubeHandle` - interface towards tube related commands
- `Zlikavac32\BeanstalkdLib\JobHandle` - interface towards job related commands

To express higher level API, interface methods are no longer directly mapped to Beanstalkd commands.

Status responses have their own classes which expose readable interface to those values, and values themselves are strictly typed.

Default implementations are backend by `Protocol` and are provided in:

- `Zlikavac32\BeanstalkdLib\Client\ProtocolClient`
- `Zlikavac32\BeanstalkdLib\Client\ProtocolTubeHandle`
- `Zlikavac32\BeanstalkdLib\Client\ProtocolJobHandle`

### Framework agnostic

Library itself is agnostic to any framework. Adapters are currently provided only for Symfony. Where possible, library is also agnostic to other libraries and extensions. For example, yaml parsing, socket access and json serialization are defined through interfaces required by this library, and adapters are provided for extensions and libraries.

### Various signal handling strategies

`Zlikavac32\BeanstalkdLib\SignalHandlerInstaller` installs injected `Zlikavac32\BeanstalkdLib\InterruptHandler` to handle `SIGINT`, `SIGTERM` and `SIGQUIT`.

Existing handling strategies are:

- `Zlikavac32\BeanstalkdLib\InterruptHandler\DoNothingInterruptHandler` - just ignores signal
- `Zlikavac32\BeanstalkdLib\InterruptHandler\GracefulExitInterruptHandler` - mark that graceful exit is in progress
- `Zlikavac32\BeanstalkdLib\InterruptHandler\HardInterruptHandler` - on second signal throws interrupt exception
- `Zlikavac32\BeanstalkdLib\InterruptHandler\TimeoutHardInterruptHandler` - schedules interrupt exception to be thrown in N seconds

### Graceful exit support

`Zlikavac32\BeanstalkdLib\GracefulExit` can be used to inspect whether graceful exit is in progress, and if so, no more processing should be done. By default, graceful exit inspection is provided through `Zlikavac32\BeanstalkdLib\InterruptHandler\GracefulExitInterruptHandler`.

Job dispatcher and protocol inspect value to break from waiting.

### Oriented towards decorators

Since the idea is to keep classes thin, more functionality is provided by decorating original service.

For example, sockets can be made lazy with `Zlikavac32\BeanstalkdLib\Socket\LazySocket` and have exclusive access (due to the signals) with `Zlikavac32\BeanstalkdLib\Socket\ExclusiveAccessSocket`.

### Auto-touch job

`Zlikavac32\BeanstalkdLib\Runner\AutoTouchRunner` is runner decorator that will asynchronously touch job if his time to run is running out.

### Tube specific configuration

Default values are defined on tube configuration, which client uses when no other value is provided. That means that different tubes can have different default values, like time to run, or serializer used.

## Installation

Recommended installation is through Composer.

```
composer require zlikavac32/beanstalkd-lib
```

## Usage

Next, we'll explore configuration and usage.

### Configuration

Provided protocol implementation (`Zlikavac32\BeanstalkdLib\Protocol\ProtocolOverSocket`) uses `Zlikavac32\BeanstalkdLib\Socket` to establish connection with the server. Provided adapter is for `sockets` PHP extension.

```php
$socket = new NativePHPSocket(60000000);
```

Original socket can be decorated so that it's, for example, lazily loadad.

```php
$socket = new LazySocket($socket);
```

Provided protocol implementation also requires `Zlikavac32\BeanstalkdLib\YamlParser` for protocol parsing. Adapter is provided Symfony YAML component.

```php
$yamlParser = new SymfonyYamlParser();
```

One last thing that protocol requires is graceful exit object (instance of `Zlikavac32\BeanstalkdLib\GracefulExit`) to determine whether we should break from waiting.

```php
$gracefulExit = new GracefulExitInterruptHandler();
```

Now we can create our protocol.

```php
$protocol = new ProtocolOverSocket(
    $socket->open('127.0.0.1', 11300),
    $gracefulExit,
    $yamlParser
);
```

Protocol on it's own does not bring any great benefit to our code, and that's where `Zlikavac32\BeanstalkdLib\Client` comes into play.

Since client abstracts tube access, we can reduce number of use tube commands by decorating protocol to be state aware.

```php
$protocol = new StateAwareProtocol($protocol);
```

Next we need tube configuration for each tube that we want the system to know about. Every tube configuration requires serializer implemented as `Zlikavac32\BeanstalkdLib\Serializer`. For domain objects custom serializers can be implemented, or a generic serializer like `Zlikavac32\BeanstalkdLib\Adapter\PHP\Json\NativePHPJsonSerializer` can be used.

```php
$jsonSerializer = new NativePHPJsonSerializer(true);
$fooTubeConfiguration = new StaticTubeConfiguration(0, 1024, 300, 3600, $jsonSerializer)
```

Now we can create our client. Provided client requires factory for tube configurations.

```php

$tubeConfigurationFactory = new TubeMapConfigurationFactory(new Map([
    'foo' => $fooTubeConfiguration,
]));

$client = new ProtocolClient($protocol, $tubeConfigurationFactory);
```

### Producers

To put a job into the queue, we retrieve correct tube and put a job in it.

```php
$fooTube = $client->tube('foo');

$fooTube->put([1, 2, 3, 4]);
```

### Workers

To reserve a job, we call `reserve()` on the client.

```php
$reservedJob = $client->reserve();
```

Reserved job can then be manipulated. Configured tube serializer is used to deserialize payload.

```php
$jobId = $reservedJob->id();
$deserializedPayload = $reservedJob->payload();

$reservedJob->delete();
```

To make things easier, job dispatcher is provided in `Zlikavac32\BeanstalkdLib\JobDispatcher\TubeMapJobDispatcher`. Complete configuration, with signal handling, can be found in [examples/worker_producer](/examples/worker_producer)

## Examples

You can see more examples with code comments in [examples](/examples).
