<?php

namespace Dissonance\Providers;

use Dissonance\Discord;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class DiscordProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            Discord::class,
            function ($app) {
                /** @var Logger $monolog */
                $monolog = $app->make(Log::class)->getMonolog();
                // Adds stdout as another log handler
                $monolog->pushHandler(new StreamHandler('php://stdout'));

                return new Discord([
                    'token' => $app['config']->get('discord.token'),
                    'logger' => $monolog,
                    'logging' => true,
                    'loggerLevel' => $app['config']->get('app.log_level') == 'debug' ? Logger::DEBUG : Logger::INFO
                ]);
            }
        );
    }
}
