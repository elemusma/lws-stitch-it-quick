<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Contracts\Translation;

/** @internal */
interface HasLocalePreference
{
    /**
     * Get the preferred locale of the entity.
     *
     * @return string|null
     */
    public function preferredLocale();
}
