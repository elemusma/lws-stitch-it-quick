<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Contracts\Validation;

/** @internal */
interface ValidatesWhenResolved
{
    /**
     * Validate the given class instance.
     *
     * @return void
     */
    public function validateResolved();
}
