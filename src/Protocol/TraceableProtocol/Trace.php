<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib\Protocol\TraceableProtocol;

use Ds\Hashable;

class Trace implements Hashable
{

    /**
     * @var string
     */
    private $method;
    /**
     * @var array
     */
    private $arguments;
    /**
     * @var string
     */
    private $hash = null;

    public function __construct(string $method, array $arguments)
    {
        $this->method = $method;
        $this->arguments = $arguments;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function arguments(): array
    {
        return $this->arguments;
    }

    public function __toString(): string
    {
        return sprintf('{%s, %s}', $this->method, $this->stringifyArray($this->arguments));
    }

    private function stringifyArray(array $array): string
    {
        $str = [];

        $includeKey = range(0, count($array) - 1) !== array_keys($array);

        foreach ($array as $k => $v) {
            switch (true) {
                case is_object($v):
                    if (method_exists($v, '__toString')) {
                        $valueAsString = var_export((string)$v, true);

                        break;
                    }

                    $valueAsString = sprintf('(instance of %s)', get_class($v));

                    break;
                case is_array($v):
                    $valueAsString = $this->stringifyArray($v);

                    break;
                default:
                    $valueAsString = var_export($v, true);

                    break;
            }

            if ($includeKey) {
                $str[] = sprintf('%s: %s', var_export($k, true), $valueAsString);

                continue;
            }

            $str[] = $valueAsString;
        }

        if ($includeKey) {
            return sprintf('{%s}', implode(', ', $str));
        }

        return sprintf('[%s]', implode(', ', $str));
    }

    function hash(): string
    {
        if (null === $this->hash) {
            $this->hash = sha1($this->method."|".serialize($this->arguments));
        }

        return $this->hash;
    }

    function equals($obj): bool
    {
        if (!$obj instanceof Trace) {
            return false;
        }

        return $this->method === $obj->method &&
            $this->arguments === $obj->arguments;
    }
}
