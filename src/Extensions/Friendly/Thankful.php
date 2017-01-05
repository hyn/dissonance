<?php

namespace Dissonance\Extensions\Friendly;

use Discord\Parts\Channel\Message;
use Dissonance\Bot;
use Dissonance\Contracts\Extension;
use Dissonance\Discord;
use Dissonance\Traits\WorksWithMessages;

class Thankful implements Extension
{
    use WorksWithMessages;
    /**
     * @var Bot
     */
    protected $bot;

    /**
     * Thankful constructor.
     * @param Bot $bot
     */
    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
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
     * @param Message $message
     * @param Discord $discord
     */
    public function youAreWelcome(Message $message, Discord $discord)
    {
        if ($this->bot->isMentioned($message) && $this->messageMatches($message, '/^(thanks|thank you)$/')) {
            $message->reply('You are welcome.');
        }
    }

    /**
     * Associative array with the event as key and the callable as value.
     *
     * @return array
     */
    public function on(): array
    {
        return [
            'message' => [$this, 'youAreWelcome']
        ];
    }
}
