<?php

namespace Dissonance\Foundation;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcherContract;
use Illuminate\Events\Dispatcher;
use josegonzalez\Dotenv\Filter\LowercaseKeyFilter;
use josegonzalez\Dotenv\Loader;
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

        $this->setSelf();
        $this->loadEnv();
        $this->loadConfiguration();
        $this->loadEvents();
    }

    /**
     * Loads variables specified in the .env file to be used
     * in the application or configuration files.
     */
    protected function loadEnv()
    {
        if (file_exists($env = $this->basePath . '/.env')) {
            (new Loader($env))->setFilters([
                LowercaseKeyFilter::class,
            ])->parse()->putenv();
        }
    }

    /**
     * Loads all configuration files.
     */
    protected function loadConfiguration()
    {
        $this->bind(Repository::class, function () {
            $files = (new Finder())
                ->files()
                ->in([
                    $this->basePath . '/config'
                ])->getIterator();

            $items = [];

            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            foreach ($files as $file) {
                $items[$file->getBasename('.php')] = include $file->getPathname();
            }

            return new Repository($items);
        });
    }

    protected function setSelf()
    {
        $this->singleton(Application::class, function ($app) {
            return $app;
        });
    }

    protected function loadEvents()
    {
        $this->singleton(EventDispatcherContract::class, function ($app) {
            return new Dispatcher($app);
        });
    }
}
