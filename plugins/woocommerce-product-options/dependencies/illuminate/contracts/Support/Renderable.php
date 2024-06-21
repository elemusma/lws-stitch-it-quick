<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Contracts\Support;

/** @internal */
interface Renderable
{
    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render();
}
