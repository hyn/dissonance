<?php

$autoloaders = [
    __DIR__.'/../vendor/autoload.php',
    __DIR__.'/../../../autoload.php'
];
foreach ($autoloaders as $autoload) {
    if (file_exists($autoload)) {
        require_once $autoload;
        break;
    }
}

use Dissonance\Foundation\Application;

return new Application(__DIR__ . '/../');
