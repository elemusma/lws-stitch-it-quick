<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Contracts\Foundation;

/** @internal */
interface CachesConfiguration
{
    /**
     * Determine if the application configuration is cached.
     *
     * @return bool
     */
    public function configurationIsCached();
    /**
     * Get the path to the configuration cache file.
     *
     * @return string
     */
    public function getCachedConfigPath();
    /**
     * Get the path to the cached services.php file.
     *
     * @return string
     */
    public function getCachedServicesPath();
}
