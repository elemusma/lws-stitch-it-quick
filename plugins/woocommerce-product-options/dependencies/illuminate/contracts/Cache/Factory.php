<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Contracts\Cache;

/** @internal */
interface Factory
{
    /**
     * Get a cache store instance by name.
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Cache\Repository
     */
    public function store($name = null);
}
