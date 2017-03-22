<?php

namespace Dissonance\Console;

use Illuminate\Foundation\Console\Kernel as Foundation;

class Kernel extends Foundation
{
    protected $commands = [
        Runner::class,
    ];

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        return array_merge($this->bootstrappers, [
            LoadExtensions::class,
        ]);
    }
}
