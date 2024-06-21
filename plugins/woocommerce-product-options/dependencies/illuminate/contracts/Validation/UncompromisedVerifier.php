<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Contracts\Validation;

/** @internal */
interface UncompromisedVerifier
{
    /**
     * Verify that the given data has not been compromised in data leaks.
     *
     * @param  array  $data
     * @return bool
     */
    public function verify($data);
}
