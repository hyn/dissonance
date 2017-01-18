<?php

namespace Dissonance\Extensions\Versioning;

use Discord\Wrapper\LoggerWrapper;
use Dissonance\Abstracts\AbstractExtension;
use Dissonance\Traits\WorksWithMessages;
use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Gitlab extends AbstractExtension
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

    public function reply()
    {
        if (!$this->isMentioned) {
            return;
        }

        $slug = $this->message->channel->name;

        if (!preg_match($this->regex, $this->message->getCleaned(), $m)) {
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
            $this->response('Get out :clown:, that project doesn\'t exist.');
            return;
        }

        if ($issue = Arr::get($m, 'issue')) {
            $issue = ltrim($issue, '#');
            $issue = $this->projectIssue($project['id'], $issue);

            if (!$issue) {
                $this->response('That issue does not exist.');
            }

            if ($issue) {
                $this->response($this->formatIssueReply($issue));
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
     * @param string $path
     * @return Collection|null
     */
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
