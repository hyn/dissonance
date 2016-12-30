<?php

namespace Dissonance\Extensions\Welcoming;

use Discord\WebSockets\Events\PresenceUpdate as Event;
use Discord\Parts\WebSockets\PresenceUpdate as Part;
use Dissonance\Contracts\Extension;


class WelcomesOnline implements Extension
{

    /**
     * @param Part $new
     * @param Part $old
     */
    public function welcomeBack(Part $new, Part $old)
    {
        if (!$old->status) {
            return;
        }

        if ($old->status === 'offline' && $new->status === 'online') {
            $new->user->sendMessage(
                "Welcome back {$new->user->username}."
            );
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
            Event::class => [$this, 'welcomeBack']
        ];
    }
}
