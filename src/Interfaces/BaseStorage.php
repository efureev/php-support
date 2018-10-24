<?php

namespace Php\Support\Interfaces;

interface BaseStorage
{
    /**
     * Create storage.
     *
     * @return void
     */
    public function init(): void;

    /**
     * Drop storage.
     *
     * @return bool
     */
    public function drop(): bool;


    /**
     * Clear contents from storage
     *
     * @return bool
     */
    public function clear(): bool;
}
