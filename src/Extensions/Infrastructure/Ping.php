<?php

namespace Dissonance\Extensions\Infrastructure;

use Dissonance\Abstracts\AbstractExtension;

class Ping extends AbstractExtension
{
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
     * @return void
     */
    protected function reply()
    {
        if ($this->isMentioned && $this->message->is('ping')) {
            $this->response("pong, duration: {$this->message->timestamp->diffForHumans(null, true)}");
        }
    }
}
