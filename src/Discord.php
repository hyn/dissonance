<?php

namespace Dissonance;

use Discord\Discord as Native;
use Discord\Parts\User\Client;
use Discord\Wrapper\LoggerWrapper;
use React\EventLoop\LoopInterface;

/**
 * @property Client $client
 * @property LoggerWrapper $logger
 * @property LoopInterface $loop
 * @property array $options
 */
class Discord extends Native
{
    /**
     * @param string $name
     * @return \Discord\Parts\User\Client|mixed
     */
    public function __get($name)
    {
        if ($name === 'client') {
            return $this->client;
        }

        return parent::__get($name);
    }
}
