<?php

namespace Dissonance\Console;

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
     * @return string
     */
    protected function getInstalled()
    {
        return (new Filesystem())->get($this->app->vendorPath() . 'composer/installed.json');
    }
}
