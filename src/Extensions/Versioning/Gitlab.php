<?php

namespace Dissonance\Extensions\Versioning;

use Discord\Parts\Channel\Message;
use Dissonance\Contracts\Extension;
use Dissonance\Discord;
use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;

class Gitlab implements Extension
{
    protected $baseUrl = 'https://gitlab.example.com/api/v3';

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
        $project = $message->channel->name;
        if ($namespace = $this->config->get('gitlab.namespace')) {
            $project = "$namespace/$project";
        }

        $project = $this->project($project);

        
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
