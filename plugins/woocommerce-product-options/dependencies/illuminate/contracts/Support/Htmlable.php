<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Contracts\Support;

/** @internal */
interface Htmlable
{
    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml();
}
