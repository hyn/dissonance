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
        foreach ([
                     $this->basePath . DIRECTORY_SEPARATOR . 'bootstrap',
                     __DIR__ . '/../../bootstrap'
                 ] as $bootstrapPath) {
            if (is_dir($bootstrapPath)) {
                return $bootstrapPath;
            }
        }
    }

    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath()
    {
        return null;
    }

    /**
     * Get the path to the vendor directory.
     *
     * @return string
     */
    public function vendorPath()
    {
        return base_path('vendor/');
    }
}
