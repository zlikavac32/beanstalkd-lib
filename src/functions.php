<?php

declare(strict_types=1);

namespace Zlikavac32\BeanstalkdLib;

function microTimeToHuman(float $time): string {
    $sec = (int) $time;
    $fraction = (int) (($time - $sec) * 1e6);

    $parts = [
        [(int) ($sec / 3600), 'h'],
        [(int) (($sec % 3600) / 60), 'min'],
        [$sec % 60, 's'],
        [(int) ($fraction / 1000), 'ms'],
        [$fraction % 1000, 'us']
    ];

    $str = '';

    for ($i = 0, $c = count($parts); $i < $c; $i++) {
        [$value, $unit] = $parts[$i];

        if ($value === 0) {
            continue ;
        }

        $str = $value . ' ' . $unit;

        break ;
    }

    $i++;

    if ($i >= count($parts)) {
        return $str;
    }

    [$value, $unit] = $parts[$i];

    if ($value === 0) {
        return $str;
    }

    return $str . ' ' . $value . ' ' . $unit;
}
