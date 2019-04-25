<?php

declare(strict_types=1);

require_once __DIR__.'/common.php';

$indexProjectCommitTube = $client->tube(TUBE_INDEX_PROJECT_COMMIT);

$indexProjectCommitTube->put(
    new ProjectCommit(
        'foo/bar',
        sha1((string)microtime(true))
    )
);
