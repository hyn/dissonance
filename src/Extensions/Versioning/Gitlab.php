<?php

namespace Dissonance\Extensions\Versioning;

use Discord\Parts\Channel\Message;
use Discord\Wrapper\LoggerWrapper;
use Dissonance\Contracts\Extension;
use Dissonance\Discord;
use Dissonance\Traits\WorksWithMessages;
use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Gitlab implements Extension
{
    use WorksWithMessages;

    protected $baseUrl = 'https://gitlab.com/api/v3/';

    protected $regex = '/(?<project>[\S]+)?(?<issue>\#[0-9]+)/';

    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var LoggerWrapper
     */
    protected $logger;

    public function __construct(Repository $config, LoggerWrapper $logger)
    {
        $this->config = $config;
        $this->logger = $logger;

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
        if (! \Discord\mentioned($discord->client->user, $message)) {
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
            $this->logger->debug("Project $slug does not exist.");
            $message->reply('Get out :clown:, that project doesn\'t exist.');
            return;
        }

        if ($issue = Arr::get($m, 'issue')) {
            $issue = ltrim($issue, '#');
            $issue = $this->projectIssue($project['id'], $issue);

            if (!$issue) {
                $message->reply('That issue does not exist.');
            }

            if ($issue) {
                $message->reply($this->formatIssueReply($issue));
            }
            return;
        }
    }

    /**
     * @param array $issue
     * @return string
     */
    protected function formatIssueReply(array $issue): string
    {
        $reply = sprintf(
            'Issue #%d (%s) - %s',
            Arr::get($issue, 'id'),
            Arr::get($issue, 'state'),
            Arr::get($issue, 'title')
        );

        if (Arr::get($issue, 'assignee') && $name = Arr::get($issue, 'assignee.name')) {
            $reply .= " (assigned to $name)";
        }

        return $reply;
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
        $path = ltrim($path, '/');

        try {
            $response = $this->guzzle->get($path);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
        }

        if ($response->getStatusCode() === 200) {
            return collect(json_decode($response->getBody(), true));
        }

        return null;
    }

    /**
     * @param string $slug
     * @return Collection|null
     */
    protected function project(string $slug)
    {
        return $this->get('/projects/' . urlencode($slug));
    }

    /**
     * @param int $project
     * @param int $issue
     * @return Collection|null
     */
    protected function projectIssue(int $project, int $issue)
    {
        return $this->get('/projects/' . $project . '/issues/' . $issue);
    }
}
