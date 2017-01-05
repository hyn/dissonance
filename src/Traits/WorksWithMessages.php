<?php

namespace Dissonance\Traits;

use Discord\Parts\Channel\Message;
use Illuminate\Support\Str;

trait WorksWithMessages
{
    public function cleanMessageContent(Message $message): string
    {
        $content = preg_replace('/(\<@[0-9]+\>)/i', '', $message->content);

        return trim($content);
    }

    public function messageIs(Message $message, string $is): bool
    {
        $content = $this->cleanMessageContent($message);

        return strtolower($content) === $is;
    }

    public function messageHas(Message $message, $has): bool
    {
        if (!is_array($has)) {
            $has = [$has];
        }

        $content = $this->cleanMessageContent($message);

        return Str::contains(strtolower($content), $has);
    }

    public function messageMatches(Message $message, string $match): bool
    {
        return preg_match($match, $this->cleanMessageContent($message));
    }
}
