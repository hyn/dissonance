<?php

namespace Dissonance\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message as DiscordMessage;

class Message
{
    /**
     * @var DiscordMessage
     */
    public $message;
    /**
     * @var Discord
     */
    public $discord;

    public function __construct(DiscordMessage $message, Discord $discord)
    {
        $this->message = $message;
        $this->discord = $discord;
    }
}
