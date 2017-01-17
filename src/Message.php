<?php

namespace Dissonance;

use Discord\Parts\Channel\Message as IncomingMessage;
use Illuminate\Support\Str;

/**
 * A message which is posted to a Discord text channel.
 *
 * @property string                         $id               The unique identifier of the message.
 * @property \Discord\Parts\Channel\Channel $channel          The channel that the message was sent in.
 * @property string                         $channel_id       The unique identifier of the channel that the message was went in.
 * @property string                         $content          The content of the message if it is a normal message.
 * @property int                            $type             The type of message.
 * @property Collection[User]               $mentions         A collection of the users mentioned in the message.
 * @property \Discord\Parts\User\User       $author           The author of the message.
 * @property bool                           $mention_everyone Whether the message contained an @everyone mention.
 * @property Carbon                         $timestamp        A timestamp of when the message was sent.
 * @property Carbon|null                    $edited_timestamp A timestamp of when the message was edited, or null.
 * @property bool                           $tts              Whether the message was sent as a text-to-speech message.
 * @property array                          $attachments      An array of attachment objects.
 * @property Collection[Embed]              $embeds           A collection of embed objects.
 * @property string|null                    $nonce            A randomly generated string that provides verification for the client. Not required.
 * @property Collection[Role]               $mention_roles    A collection of roles that were mentioned in the message.
 * @property bool                           $pinned           Whether the message is pinned to the channel.
 */
class Message
{
    /**
     * @var IncomingMessage
     */
    protected $incoming;

    /**
     * @var string
     */
    protected $cleaned;

    public function wrap(IncomingMessage $message)
    {
        $this->incoming = $message;

        $content = preg_replace('/(\<@[0-9]+\>)/i', '', $message->content);
        $this->cleaned = strtolower(trim($content));

        return $this;
    }

    /**
     * @param string $comparison
     * @return bool
     */
    public function is(string $comparison): bool
    {
        return $this->cleaned === $comparison;
    }

    /**
     * @param string $needle
     * @return bool
     */
    public function contains(string $needle): bool
    {
        return Str::contains($this->cleaned, $needle);
    }

    /**
     * @param string $regex
     * @return bool
     */
    public function matches(string $regex): bool
    {
        return preg_match($regex, $this->cleaned);
    }

    function __get($name)
    {
        return $this->incoming->{$name};
    }

    function __call($name, $arguments)
    {
        return call_user_func_array([$this->incoming, $name], $arguments);
    }
}
