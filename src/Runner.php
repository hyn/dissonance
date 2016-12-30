<?php

namespace Dissonance;

use Discord\Discord;
use Discord\Parts\User\Client;
use Discord\Wrapper\LoggerWrapper;
use Dissonance\Contracts\Extension;
use Dissonance\Foundation\Application;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Monolog\Logger;

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

    public function __construct(Application $app, Repository $config)
    {
        $this->app = $app;
        $this->config = $config;

        $this->discord = new Discord([
            'token' => $config->get('discord.token'),
            'loggerLevel' => $config->get('app.debug') ? Logger::DEBUG : Logger::INFO
        ]);

        $this->bindings();
    }

    /**
     * Runs the Discord bot as daemon.
     */
    public function run()
    {

        $this->discord->on('ready', function (Discord $discord) {
            $this->loadExtensions($discord);
        });

        $this->discord->run();
    }

    protected function bindings()
    {
        // Binds the discord instance into the container.
        $this->app->singleton(Discord::class, $this->discord);

        // Binds the identity the discord client uses into the container.
        $this->app->singleton(
            Client::class,
            $this->discord->factory(Client::class, $this->discord->getRawAttributes(), true)
        );

        // Binds the logger instance the discord client uses into the container.
        $this->app->singleton(
            LoggerWrapper::class,
            $this->discord->logger
        );

    }

    /**
     * @param Discord $discord
     */
    protected function loadExtensions(Discord $discord)
    {
        $this->extensions = collect($this->config->get('extensions', []))->mapWithKeys(function($class) use ($discord) {
            /** @var Extension $extension */
            $extension = $this->app->make($class);

            return [
                $class => $extension
            ];
        })->filter(function(Extension $extension) {

            return $extension->enabled();
        })->each(function(Extension $extension) use ($discord) {

            if (count($extension->on())) {
                foreach ($extension->on() as $event => $callable) {
                    if (!is_callable($callable)) {
                        throw new \RuntimeException('Not callable');
                    }

                    $discord->on($event, $callable);
                }
            }
        });
    }
}
