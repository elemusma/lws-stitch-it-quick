<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Contracts\Broadcasting;

/** @internal */
interface Factory
{
    /**
     * Get a broadcaster implementation by name.
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Broadcasting\Broadcaster
     */
    public function connection($name = null);
}
