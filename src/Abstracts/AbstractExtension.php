<?php

namespace Dissonance\Abstracts;

use Discord\Discord;
use Discord\Parts\Channel\Message as DiscordMessage;
use Dissonance\Message;
use Dissonance\Bot;
use Dissonance\Contracts\Extension as Contract;
use Dissonance\Traits\WorksWithMessages;

abstract class AbstractExtension implements Contract
{
    use WorksWithMessages;

    /**
     * @var bool
     */
    protected $isMentioned = false;
    /**
     * @var Bot
     */
    protected $bot;

    /**
     * @var Message
     */
    protected $message;
    /**
     * @return Bot
     */
    protected function bot(): Bot
    {
        return app(Bot::class);
    }

    /**
     * Associative array with the event as key and the callable as value.
     *
     * @return array
     */
    public function on(): array
    {
        return ['message' => [$this, 'handle']];
    }

    public function handle(DiscordMessage $message, Discord $discord)
    {
        /** @var Message message */
        $this->message = app()->make(Message::class)->wrap($message);
        $this->isMentioned = $this->bot()->isMentioned($message);
        return $this->reply();
    }

    protected function channelResponse(string $response)
    {
        return $this->message->channel->sendMessage($response);
    }

    protected function response(string $response)
    {
        return $this->message->reply($response);
    }

    /**
     * @return void
     */
    abstract protected function reply();
}
