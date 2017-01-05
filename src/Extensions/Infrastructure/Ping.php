<?php

namespace Dissonance\Extensions\Infrastructure;

use Discord\Parts\Channel\Message;
use Dissonance\Bot;
use Dissonance\Contracts\Extension;
use Dissonance\Discord;
use Dissonance\Traits\WorksWithMessages;

class Ping implements Extension
{
    use WorksWithMessages;

    /**
     * @var Bot
     */
    protected $bot;

    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
    }

    /**
     * @param Message $message
     * @param Discord $discord
     */
    public function pong(Message $message, Discord $discord)
    {
        if ($this->bot->isMentioned($message) && $this->messageIs($message, 'ping')) {
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
