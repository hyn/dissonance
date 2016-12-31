<?php

namespace Dissonance\Extensions\Infrastructure;

use Discord\Parts\Channel\Message;
use Discord\Parts\User\Client;
use Dissonance\Contracts\Extension;
use Dissonance\Discord;
use Dissonance\Traits\MutatesMessages;

class Ping implements Extension
{
    use MutatesMessages;

    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param Message $message
     * @param Discord $discord
     */
    public function pong(Message $message, Discord $discord)
    {
        $content = $this->cleanMessageContent($message);

        if (\Discord\mentioned($this->client->user, $message) && strtolower($content) === 'ping') {
            $message->reply("pong, duration: {$message->timestamp->diffForHumans(null, true)}");
        }
    }

    /**
     * Indicates whether the extension is enabled.
     *
     * @return bool
     */
    public function enabled(): bool
    {
        return true;
    }

    /**
     * Associative array with the event as key and the callable as value.
     *
     * @return array
     */
    public function on(): array
    {
        return [
            'message' => [$this, 'pong']
        ];
    }
}
