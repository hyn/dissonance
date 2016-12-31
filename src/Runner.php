<?php

namespace Dissonance;

use Discord\Parts\User\Client;
use Discord\Wrapper\LoggerWrapper;
use Dissonance\Contracts\Extension;
use Dissonance\Foundation\Application;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;

class Runner
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var Discord
     */
    protected $discord;

    /**
     * @var Collection
     */
    protected $extensions;

    public function __construct(Application $app, Repository $config, Discord $discord)
    {
        $this->app = $app;
        $this->config = $config;
        $this->discord = $discord;
    }

    /**
     * Runs the Discord bot as daemon.
     */
    public function run()
    {
        $this->discord->on('ready', function (Discord $discord) {
            $this->bindings($discord);
            $this->loadExtensions($discord);
        });

        $this->discord->run();
    }

    /**
     * @param Discord $discord
     */
    public function bindings(Discord $discord)
    {
        // Binds the identity the discord client uses into the container.
        $this->app->singleton(
            Client::class,
            function() use ($discord) { return $discord->client; }
        );

        // Binds the logger instance the discord client uses into the container.
        $this->app->singleton(
            LoggerWrapper::class,
            function() use ($discord) { return $discord->logger; }
        );

    }

    /**
     * @param Discord $discord
     */
    public function loadExtensions(Discord $discord)
    {
        $this->extensions = collect($this->config->get('extensions', []))->mapWithKeys(function($class) use ($discord) {
            /** @var Extension $extension */
            $extension = $this->app->make($class);

            return [
                $class => $extension
            ];
        })->filter(function(Extension $extension) {
            return $extension->enabled();
        })->each(function(Extension $extension, $class) use ($discord) {
            if (count($extension->on())) {
                foreach ($extension->on() as $event => $callable) {
                    if (!is_callable($callable)) {
                        throw new \RuntimeException('Not callable');
                    }

                    $discord->logger->debug("Class $class registered on event $event");

                    $discord->on($event, $callable);
                }
            }
        });
    }
}
