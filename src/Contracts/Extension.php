<?php

namespace Dissonance\Contracts;

interface Extension
{
    /**
     * Indicates whether the extension is enabled.
     *
     * @return bool
     */
    public function enabled(): bool;

    /**
     * Associative array with the event as key and the callable as value.
     *
     * @return array
     */
    public function on(): array;
}
