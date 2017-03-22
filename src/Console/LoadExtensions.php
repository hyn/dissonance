<?php

namespace Dissonance\Console;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;

class LoadExtensions
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        if ($app->configurationIsCached()) {
            return;
        }

        $this->app = $app;
    }

    /**
     * @return array
     */
    protected function getInstalled(): array
    {
        try {
            $json = (new Filesystem())->get($this->app->vendorPath() . 'composer/installed.json');
            return json_decode($json, true);
        } catch (FileNotFoundException $e) {
            return [];
        }
    }
}
