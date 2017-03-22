<?php

namespace Dissonance\Extensions;

use Illuminate\Contracts\Foundation\Application;

class Manager
{
    /**
     * @var Application
     */
    protected $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }
}
