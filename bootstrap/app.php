<?php

$basePaths = [
    __DIR__ . '/../',
    __DIR__ . '/../../../../',
];

foreach ($basePaths as $basePath) {
    if (file_exists($autoloader = $basePath . 'vendor/autoload.php')) {
        require_once $autoloader;
        break;
    }
}

use Dissonance\Foundation\Application;

return new Application($basePath);
