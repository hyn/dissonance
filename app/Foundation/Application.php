<?php

namespace Dissonance\Foundation;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Symfony\Component\Finder\Finder;

class Application extends Container implements ContainerContract
{
    /**
     * Base path of app.
     * @var string
     */
    protected $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');

        $this->configurations();
    }

    public function configurations()
    {
        $this->bind(Repository::class, function ($app) {
            $files = (new Finder())
                ->files()
                ->path(
                    $this->basePath . '/config'
                )->getIterator();

            $items = [];

            foreach($files as $file) {
                $items[$file->getBasename('.php')] = include $file->getPath();
            }

            return new Repository($items);
        });
    }
}