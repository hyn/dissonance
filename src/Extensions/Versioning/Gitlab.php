<?php

namespace Dissonance\Extensions\Versioning;

use Discord\Parts\Channel\Message;
use Dissonance\Contracts\Extension;
use Dissonance\Discord;
use Dissonance\Traits\MutatesMessages;
use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Gitlab implements Extension
{
    use MutatesMessages;

    protected $baseUrl = 'https://gitlab.example.com/api/v3';

    protected $regex = '/(?<project>[a-zA-Z0-9_-\/])?(?<issue>\#[0-9]+)/';

    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * @var Repository
     */
    protected $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;

        if ($config->get('gitlab.token')) {
            $this->guzzle = new Client([
                'base_uri' => $this->baseUrl,
                'headers' => [
                    'PRIVATE-TOKEN' => $config->get('gitlab.token')
                ]
            ]);
        }
    }

    /**
     * @param Message $message
     * @param Discord $discord
     */
    public function type(Message $message, Discord $discord)
    {
        if (! \Discord\mentioned($discord->client, $message)) {
            return;
        }

        $slug = $message->channel->name;

        if (!preg_match($this->regex, $this->cleanMessageContent($message), $m)) {
            return;
        }
        if (Arr::get($m, 'project')) {
            $slug = Arr::get($m, 'project');
        }

        if ($slug && !Str::contains($slug, '/') && ($namespace = $this->config->get('gitlab.namespace'))) {
            $slug = "$namespace/$slug";
        }

        $project = $this->project($slug);

        if (!$project) {
            $message->reply('Get out :clown:, that project doesn\'t exist.');
            return;
        }

        if ($issue = Arr::get($m, 'issue')) {
            $issue = $this->projectIssue($slug, $issue);
            $message->reply(sprintf(
                'Issue #%d (%s) - %s (assigned to %s)',
                Arr::get($issue, 'id'),
                Arr::get($issue, 'state'),
                Arr::get($issue, 'title'),
                Arr::get($issue, 'assignee.name')
            ));
            return;
        }
    }

    /**
     * Indicates whether the extension is enabled.
     *
     * @return bool
     */
    public function enabled(): bool
    {
        return !is_null($this->config->get('gitlab.token'));
    }

    /**
     * Associative array with the event as key and the callable as value.
     *
     * @return array
     */
    public function on(): array
    {
        return [
            'message' => [$this, 'type']
        ];
    }

    protected function get(string $path): ?Collection
    {
        try {
            $response = $this->guzzle->get($path);
        } catch (\Exception $e) {
            return null;
        }

        if ($response->getStatusCode() === 200) {
            return collect(json_decode($response->getBody(), true));
        }

        return null;
    }

    protected function project(string $slug)
    {
        return $this->get('/projects/' . urlencode($slug));
    }

    protected function projectIssue(string $slug, int $issue)
    {
        return $this->get('/projects/' . urlencode($slug) . '/issues/' . $issue);
    }
}
