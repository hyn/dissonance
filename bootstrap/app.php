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

$app = new Application($basePath);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    Dissonance\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    Illuminate\Foundation\Exceptions\Handler::class
);

return $app;
