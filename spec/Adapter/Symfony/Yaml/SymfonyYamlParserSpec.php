<?php

declare(strict_types=1);

namespace spec\Zlikavac32\BeanstalkdLib\Adapter\Symfony\Yaml;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Yaml\Exception\ParseException;
use Zlikavac32\BeanstalkdLib\Adapter\Symfony\Yaml\SymfonyYamlParser;
use Zlikavac32\BeanstalkdLib\YamlParseException;

class SymfonyYamlParserSpec extends ObjectBehavior {

    public function it_is_initializable(): void {
        $this->shouldHaveType(SymfonyYamlParser::class);
    }

    public function it_should_throw_exception_on_invalid_yaml(): void {
        $content = ' -';

        $this->shouldThrow(
            new YamlParseException('An error occurred while parsing YAML', $content, new ParseException(''))
        )
            ->duringParse($content);
    }

    public function it_should_throw_exception_if_parsed_result_is_not_array(): void {
        $content = '123';

        $this->shouldThrow(
            new YamlParseException('Expected an array but got "integer"', $content)
        )
            ->duringParse($content);
    }

    public function it_should_parse_content(): void {
        $content = <<<'YAML'
[123, 345]
YAML;

        $this->parse($content)->shouldReturn([123, 345]);
    }
}
