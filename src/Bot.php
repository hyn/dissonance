<?php

namespace Dissonance;

use Discord\Parts\Channel\Message;
use Discord\Parts\User\Client;

/**
 * The client is the main interface for the client. Most calls on the main class are forwarded here.
 *
 * @property string $id            The unique identifier of the client.
 * @property string $username      The username of the client.
 * @property string $password      The password of the client (if they have provided it).
 * @property string $email         The email of the client.
 * @property bool $verified      Whether the client has verified their email.
 * @property string $avatar        The avatar URL of the client.
 * @property string $avatar_hash   The avatar hash of the client.
 * @property string $discriminator The unique discriminator of the client.
 * @property bool $bot           Whether the client is a bot.
 * @property \Discord\Parts\User\User $user          The user instance of the client.
 * @property \Discord\Parts\OAuth\Application $application   The OAuth2 application of the bot.
 * @property \Discord\Repository\GuildRepository $guilds
 * @property \Discord\Repository\PrivateChannelRepository $private_channels
 * @property \Discord\Repository\UserRepository $users
 */
class Bot
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    function __get($name)
    {
        return $this->client->{$name};
    }

    /**
     * @param Message $message
     * @return bool
     */
    public function isMentioned(Message $message): bool
    {
        return \Discord\mentioned($this->client->user, $message);
    }
}
