<?php

namespace Dissonance\Events;

use Discord\Discord;

class Ready
{
    /**
     * @var Discord
     */
    public $discord;

    function __construct(Discord $discord)
    {
        $this->discord = $discord;
    }
}
