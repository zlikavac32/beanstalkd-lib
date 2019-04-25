<?php

declare(strict_types=1);

use Ds\Map;
use Zlikavac32\BeanstalkdLib\Client\ProtocolClient;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\StaticTubeConfiguration;
use Zlikavac32\BeanstalkdLib\Client\TubeConfiguration\TubeMapConfigurationFactory;
use Zlikavac32\BeanstalkdLib\Serializer;

require_once __DIR__.'/../common.php';

// Domain object
class ProjectCommit
{

    /**
     * @var string
     */
    private $project;
    /**
     * @var string
     */
    private $commit;

    public function __construct(string $project, string $commit)
    {
        $this->project = $project;
        $this->commit = $commit;
    }

    public function project(): string
    {
        return $this->project;
    }

    public function commit(): string
    {
        return $this->commit;
    }
}

// Serializer for our domain object
class ProjectCommitSerializer implements Serializer
{

    /**
     * @inheritdoc
     */
    public function serialize($payload): string
    {
        assert($payload instanceof ProjectCommit);

        return $payload->project().'|'.$payload->commit();
    }

    /**
     * @inheritdoc
     */
    public function deserialize(string $payload)
    {
        assert(strpos($payload, '|') !== false);

        [$project, $commit] = explode('|', $payload);

        return new ProjectCommit($project, $commit);
    }
}

const TUBE_INDEX_PROJECT_COMMIT = 'index.project_commit';

$serializer = new ProjectCommitSerializer();

$tubeConfigurationFactory = new TubeMapConfigurationFactory(new Map([
    TUBE_INDEX_PROJECT_COMMIT => new StaticTubeConfiguration(0, 1024, 300, 3600, $serializer),
]));

$client = new ProtocolClient($protocol, $tubeConfigurationFactory);