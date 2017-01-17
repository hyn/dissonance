<?php

namespace Dissonance\Foundation;

use Illuminate\Foundation\Application as Foundation;

class Application extends Foundation
{


    /**
     * Get the path to the bootstrap directory.
     *
     * @return string
     */
    public function bootstrapPath()
    {
        foreach([
            $this->basePath.DIRECTORY_SEPARATOR.'bootstrap',
            __DIR__ . '/../../bootstrap'
        ] as $bootstrapPath) {
            if (is_dir($bootstrapPath)) {
                return $bootstrapPath;
            }
        }
    }
}
