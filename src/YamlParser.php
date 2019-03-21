<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

/**
 * Parser for Beasntalkd YAML response
 */
interface YamlParser
{

    /**
     * @param string $content
     *
     * @return array
     *
     * @throws YamlParseException If something went wrong
     */
    public function parse(string $content): array;
}
