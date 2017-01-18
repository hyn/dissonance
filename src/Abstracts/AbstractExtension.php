<?php

namespace Dissonance\Abstracts;

use Discord\Discord;
use Discord\Parts\Channel\Message as DiscordMessage;
use Dissonance\Bot;
use Dissonance\Contracts\Extension as Contract;
use Dissonance\Message;
use Illuminate\Support\Collection;
use React\Promise\Promise;

abstract class AbstractExtension implements Contract
{
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

    /**
     * @param string|array|Collection $response
     * @param bool $sequentially
     * @return Promise
     */
    protected function channelResponse($response, bool $sequentially = false): Promise
    {
        if ($response instanceof Collection) {
            $response = $response->toArray();
        }

        if (is_string($response)) {
            return $this->message->channel->sendMessage($response);
        }

        /** @var Promise|null $promise */
        $promise = null;

        foreach ($response as $resp) {
            if ($sequentially || !$promise) {
                $promise = $this->message->channel->sendMessage($resp);
            } else {
                $promise->then(function () use ($resp, &$promise) {
                    $promise = $this->message->channel->sendMessage($resp);
                });
            }
        }

        return $promise;
    }

    protected function response(string $response): Promise
    {
        return $this->message->reply($response);
    }

    /**
     * @return void
     */
    abstract protected function reply();
}
