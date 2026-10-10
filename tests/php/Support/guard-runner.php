<?php

if ($argc < 3) {
    fwrite(STDERR, 'usage: guard-runner.php <target> <sentinel>' . PHP_EOL);
    exit(1);
}

$target = $argv[1];
$sentinel = $argv[2];

if (!is_file($target)) {
    fwrite(STDERR, 'missing target' . PHP_EOL);
    exit(1);
}

include $target;
echo $sentinel;
