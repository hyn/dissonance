<?php

namespace Dissonance\Commands;

use Discord\Wrapper\LoggerWrapper;
use Dissonance\Bot;
use Dissonance\Contracts\Extension;
use Dissonance\Discord;
use Illuminate\Container\Container as Application;
use Illuminate\Config\Repository;
use Illuminate\Console\Command;

class Runner extends Command
{
    protected $signature = 'run';
    protected $description = 'Runs the bot';

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

    public function __construct(Application $app, Discord $discord, Repository $config)
    {
        parent::__construct();

        $this->app = $app;
        $this->config = $config;
        $this->discord = $discord;
    }

    public function handle()
    {
        $this->discord->on('ready', function (Discord $discord) {
            $this->bindings($discord);
            $this->loadExtensions($discord);
            $discord->logger->debug('Bindings and extensions loaded, awaiting activity..');
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
            Bot::class,
            function () use ($discord) {
                return new Bot($discord->client);
            }
        );

        // Binds the logger instance the discord client uses into the container.
        $this->app->singleton(
            LoggerWrapper::class,
            function () use ($discord) {
                return $discord->logger;
            }
        );

    }

    /**
     * @param Discord $discord
     */
    public function loadExtensions(Discord $discord)
    {
        $this->extensions = collect($this->config->get('extensions', []))->mapWithKeys(function ($class) use ($discord) {
            /** @var Extension $extension */
            $extension = $this->app->make($class);

            return [
                $class => $extension
            ];
        })->filter(function (Extension $extension) use ($discord) {
            if (!$extension->enabled()) {
                $discord->logger->info(get_class($extension) . ' was disabled.');
            }
            return $extension->enabled();
        })->each(function (Extension $extension, $class) use ($discord) {
            if (count($extension->on())) {
                foreach ($extension->on() as $event => $callable) {
                    if (!is_callable($callable)) {
                        throw new \RuntimeException('Not callable');
                    }

                    $discord->on($event, $callable);
                }

                $discord->logger->debug("Class $class registered to listen to events", $extension->on());
            }
        });
    }
}
