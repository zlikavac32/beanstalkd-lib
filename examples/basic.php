<?php

declare(strict_types=1);

use Ds\Map;
use Zlikavac32\BeanstalkdLib\Adapter\PHP\Json\NativePHPJsonSerializer;
use Zlikavac32\BeanstalkdLib\Client\ProtocolClient;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\StaticTubeConfiguration;
use Zlikavac32\BeanstalkdLib\ProtocolTubePurger\IterativeProtocolTubePurger;

require_once __DIR__.'/common.php';

// Serializer that is used to encode/decode arbitrary payload
$jsonSerializer = new NativePHPJsonSerializer(true);


// We can also provide domain specific serializers
class DomainObject
{

    /**
     * @var int
     */
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function id(): int
    {
        return $this->id;
    }
}

class DomainObjectSerializer implements \Zlikavac32\BeanstalkdLib\Serializer
{

    /**
     * @inheritdoc
     */
    public function serialize($payload): string
    {
        assert($payload instanceof DomainObject);

        return (string)$payload->id();
    }

    /**
     * @inheritdoc
     */
    public function deserialize(string $payload)
    {
        assert(preg_match('/^\d+$/', $payload));

        return new DomainObject((int)$payload);
    }
}

$domainSerializer = new DomainObjectSerializer();

$protocolTubePurger = new IterativeProtocolTubePurger();

// Client uses protocol and tube configurations
$client = new ProtocolClient($protocol, $protocolTubePurger, new Map([
    'foo' => new StaticTubeConfiguration(0, 1024, 300, 3600, $jsonSerializer),
    'bar' => new StaticTubeConfiguration(0, 1024, 600, 86400, $domainSerializer),
]));



// We can get tube handle and pass it around
$fooTube = $client->tube('foo');

// Job that is placed into queue is serialized using configured tube serializer
$fooTube->put(['a' => 'A', 'b' => 'B', 'c' => 'C']);

// Stats are type-hinted and organized
echo 'Number of ready jobs: ', $fooTube->stats()
    ->metrics()
    ->numberOfReadyJobs(), "\n";

// Job related commands return handle to the job, which can then be used to query different
// stats and/or manipulate job
$nextJob = $fooTube->peekReady();

echo 'Next ready job is with ID: ', $nextJob->id(), "\n";

echo 'Raw payload for job ', $nextJob->id(), ' is: ', $protocol->peek($nextJob->id())
    ->payload(), "\n";

// Payload from Beanstalkd is deserialized using configured tube serializer
echo 'Payload is of type: ', gettype($nextJob->payload()), "\n";

echo 'Deleting job', "\n";

$nextJob->delete();

echo 'Number of ready jobs: ', $fooTube->stats()
    ->metrics()
    ->numberOfReadyJobs(), "\n";

echo 'Adding new job', "\n";

$fooTube->put(['f' => 'F']);

echo 'Reserving job', "\n";

// To reserve a job, we first must watch tube, and then reserve a job
$client->watch('foo');

$reservedJob = $client->reserve();

echo 'Reserved job with id: ', $reservedJob->id(), "\n";

$reservedJob->delete();



// Working with domain objects is same
$barTube = $client->tube('bar');

$barTube->put(new DomainObject(32));

$nextJob = $barTube->peekReady();

echo 'Next ready job is with ID: ', $nextJob->id(), "\n";

echo 'Raw payload for job ', $nextJob->id(), ' is: ', $protocol->peek($nextJob->id())
    ->payload(), "\n";

echo 'Job payload is instance of: ', get_class($nextJob->payload()), "\n";

echo 'Deleting job', "\n";



// Tubes are tubes can be used in any order
$fooTube->put(['g' => 'G']);
$barTube->put(new DomainObject(64));
$barTube->put(new DomainObject(128));
$fooTube->put(['m' => 'M']);
$barTube->put(new DomainObject(256));

echo 'Jobs in foo: ', $fooTube->stats()->metrics()->numberOfReadyJobs(), "\n";
echo 'Jobs in bar: ', $barTube->stats()->metrics()->numberOfReadyJobs(), "\n";



// Simple iterative purger is provided as IterativeProtocolTubePurger
$protocolPurger = new IterativeProtocolTubePurger();
$protocolPurger->purge($protocol, 'foo', 'bar');

echo 'After purging', "\n";

echo 'Jobs in foo: ', $fooTube->stats()->metrics()->numberOfReadyJobs(), "\n";
echo 'Jobs in bar: ', $barTube->stats()->metrics()->numberOfReadyJobs(), "\n";

// Example output from this script
/*
Number of ready jobs: 1
Next ready job is with ID: 345
Raw payload for job 345 is: {"a":"A","b":"B","c":"C"}
Payload is of type: array
Deleting job
Number of ready jobs: 0
Adding new job
Reserving job
Reserved job with id: 346
Next ready job is with ID: 347
Raw payload for job 347 is: 32
Job payload is instance of: DomainObject
Deleting job
Jobs in foo: 2
Jobs in bar: 4
After purging
Jobs in foo: 0
Jobs in bar: 0
 */
