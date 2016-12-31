<?php

namespace Dissonance\Traits;

use Discord\Parts\Channel\Message;

trait MutatesMessages
{
    public function cleanMessageContent(Message $message): string
    {
        $content = preg_replace('/(\<@[0-9]+\>)/i', '', $message->content);

        return trim($content);
    }
}
