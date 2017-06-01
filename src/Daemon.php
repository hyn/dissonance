<?php

namespace Dissonance;

use Discord\Discord;
use Evenement\EventEmitterTrait;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;

class Daemon
{
    use EventEmitterTrait;
    /**
     * @var Discord
     */
    protected $discord;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Dispatcher
     */
    protected $events;

    public function __construct(string $token)
    {
        ini_set('memory_limit','1G');

        $this->bindings();

        $this->discord = new Discord(compact('token'));
    }

    protected function bindings()
    {
        $this->container = Container::getInstance();
        $this->events = new Dispatcher($this->container);
    }

    public function run()
    {
        $this->discord->on('ready', function ($discord) {
            $this->events->dispatch(
                new Events\Ready($discord)
            );

            $discord->on('message', function ($message, $discord) {
                $this->events->dispatch(
                    new Events\Message($message, $discord)
                );
            });
        });

        $this->discord->run();
    }
}
