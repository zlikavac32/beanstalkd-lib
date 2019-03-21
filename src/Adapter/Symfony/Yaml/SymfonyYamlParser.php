<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Adapter\Symfony\Yaml;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Zlikavac32\BeanstalkdLib\YamlParseException;
use Zlikavac32\BeanstalkdLib\YamlParser;

class SymfonyYamlParser implements YamlParser {

    /**
     * @param string $content
     *
     * @return array
     *
     * @throws YamlParseException
     */
    public function parse(string $content): array {
        try {
            $ret = Yaml::parse($content, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
        } catch (ParseException $e) {
            throw new YamlParseException('An error occurred while parsing YAML', $content, $e);
        }

        if (!is_array($ret)) {
            throw new YamlParseException(\sprintf('Expected an array but got "%s"', \gettype($ret)), $content);
        }

        return $ret;
    }
}
