<?php

namespace Dissonance\Extensions\Friendly;

use Dissonance\Abstracts\AbstractExtension;

class Thankful extends AbstractExtension
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
        if ($this->isMentioned && $this->message->matches('/^(thanks|thank you)$/')) {
            $this->message->reply('You are welcome.');
        }
    }
}
