<?php

namespace Dissonance\Console;

use Dissonance\Commands\Runner;
use Illuminate\Foundation\Console\Kernel as Foundation;

class Kernel extends Foundation
{
    protected $commands = [
        Runner::class,
    ];
}
